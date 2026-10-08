-- CRM database schema (PostgreSQL / Supabase compatible)

create extension if not exists "pgcrypto";

-- Shared updated_at trigger ------------------------------------------------
create or replace function set_updated_at() returns trigger as $$
begin
  new.updated_at = now();
  return new;
end;
$$ language plpgsql;

-- Users (sales team) -------------------------------------------------------
create table crm_users (
  id          uuid primary key default gen_random_uuid(),
  full_name   text not null,
  email       text not null unique,
  role        text not null default 'sales' check (role in ('admin','manager','sales','support')),
  is_active   boolean not null default true,
  created_at  timestamptz not null default now(),
  updated_at  timestamptz not null default now()
);

-- Companies ----------------------------------------------------------------
create table companies (
  id          uuid primary key default gen_random_uuid(),
  name        text not null,
  website     text,
  industry    text,
  size_range  text,
  phone       text,
  address     text,
  city        text,
  state       text,
  country     text,
  owner_id    uuid references crm_users(id) on delete set null,
  created_at  timestamptz not null default now(),
  updated_at  timestamptz not null default now()
);

-- Contacts -----------------------------------------------------------------
create table contacts (
  id            uuid primary key default gen_random_uuid(),
  company_id    uuid references companies(id) on delete set null,
  owner_id      uuid references crm_users(id) on delete set null,
  first_name    text not null,
  last_name     text,
  email         text unique,
  phone         text,
  job_title     text,
  linkedin_url  text,
  source        text,
  status        text not null default 'lead'
                check (status in ('lead','prospect','customer','churned','unqualified')),
  notes         text,
  created_at    timestamptz not null default now(),
  updated_at    timestamptz not null default now()
);

-- Sales pipeline -----------------------------------------------------------
create table pipeline_stages (
  id           uuid primary key default gen_random_uuid(),
  name         text not null unique,
  position     int  not null unique,
  probability  numeric(5,2) not null default 0 check (probability between 0 and 100),
  is_won       boolean not null default false,
  is_lost      boolean not null default false
);

insert into pipeline_stages (name, position, probability, is_won, is_lost) values
  ('New',          1,   10, false, false),
  ('Qualified',    2,   25, false, false),
  ('Proposal',     3,   50, false, false),
  ('Negotiation',  4,   75, false, false),
  ('Won',          5,  100, true,  false),
  ('Lost',         6,    0, false, true);

create table deals (
  id                  uuid primary key default gen_random_uuid(),
  title               text not null,
  company_id          uuid references companies(id) on delete set null,
  contact_id          uuid references contacts(id) on delete set null,
  owner_id            uuid references crm_users(id) on delete set null,
  stage_id            uuid not null references pipeline_stages(id),
  amount              numeric(14,2) not null default 0,
  currency            char(3) not null default 'INR',
  expected_close_date date,
  closed_at           timestamptz,
  lost_reason         text,
  created_at          timestamptz not null default now(),
  updated_at          timestamptz not null default now()
);

-- Activities (calls, emails, meetings, notes) ------------------------------
create table activities (
  id           uuid primary key default gen_random_uuid(),
  type         text not null check (type in ('call','email','meeting','note','task')),
  subject      text not null,
  description  text,
  contact_id   uuid references contacts(id) on delete cascade,
  company_id   uuid references companies(id) on delete cascade,
  deal_id      uuid references deals(id) on delete cascade,
  owner_id     uuid references crm_users(id) on delete set null,
  due_at       timestamptz,
  completed_at timestamptz,
  created_at   timestamptz not null default now(),
  updated_at   timestamptz not null default now()
);

-- Tags ---------------------------------------------------------------------
create table tags (
  id    uuid primary key default gen_random_uuid(),
  name  text not null unique,
  color text
);

create table contact_tags (
  contact_id uuid not null references contacts(id) on delete cascade,
  tag_id     uuid not null references tags(id) on delete cascade,
  primary key (contact_id, tag_id)
);

create table deal_tags (
  deal_id uuid not null references deals(id) on delete cascade,
  tag_id  uuid not null references tags(id) on delete cascade,
  primary key (deal_id, tag_id)
);

-- Indexes ------------------------------------------------------------------
create index on companies (owner_id);
create index on contacts (company_id);
create index on contacts (owner_id);
create index on contacts (status);
create index on deals (company_id);
create index on deals (contact_id);
create index on deals (owner_id);
create index on deals (stage_id);
create index on activities (contact_id);
create index on activities (company_id);
create index on activities (deal_id);
create index on activities (owner_id, due_at) where completed_at is null;

-- updated_at triggers ------------------------------------------------------
create trigger trg_crm_users_updated  before update on crm_users  for each row execute function set_updated_at();
create trigger trg_companies_updated  before update on companies  for each row execute function set_updated_at();
create trigger trg_contacts_updated   before update on contacts   for each row execute function set_updated_at();
create trigger trg_deals_updated      before update on deals      for each row execute function set_updated_at();
create trigger trg_activities_updated before update on activities for each row execute function set_updated_at();

-- Row Level Security (Supabase): enable now, add policies per your auth model.
alter table crm_users       enable row level security;
alter table companies       enable row level security;
alter table contacts        enable row level security;
alter table pipeline_stages enable row level security;
alter table deals           enable row level security;
alter table activities      enable row level security;
alter table tags            enable row level security;
alter table contact_tags    enable row level security;
alter table deal_tags       enable row level security;
