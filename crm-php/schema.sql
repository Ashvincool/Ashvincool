-- Keshav Technosys CRM — database schema v2 (MySQL 5.7+ / MariaDB 10.3+)
-- Import into an EMPTY database. Tables: users, pipeline_stages, companies, contacts, deals, activities.

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'sales',      -- admin | sales
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pipeline_stages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL UNIQUE,
  position INT NOT NULL,
  probability TINYINT UNSIGNED NOT NULL DEFAULT 0,  -- % chance to win
  is_won TINYINT(1) NOT NULL DEFAULT 0,
  is_lost TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO pipeline_stages (name, position, probability, is_won, is_lost) VALUES
  ('New',1,10,0,0), ('Qualified',2,25,0,0), ('Proposal',3,50,0,0),
  ('Negotiation',4,75,0,0), ('Won',5,100,1,0), ('Lost',6,0,0,1);

CREATE TABLE companies (
  id VARCHAR(24) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  website VARCHAR(255), industry VARCHAR(120), phone VARCHAR(60), city VARCHAR(120),
  notes TEXT,
  owner_id INT UNSIGNED NULL,
  created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY (name), KEY (owner_id),
  FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE contacts (
  id VARCHAR(24) PRIMARY KEY,
  first_name VARCHAR(120) NOT NULL, last_name VARCHAR(120),
  email VARCHAR(190), phone VARCHAR(60), job_title VARCHAR(160),
  company_id VARCHAR(24) NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'lead',     -- lead|prospect|customer|churned|unqualified
  source VARCHAR(60), notes TEXT,
  owner_id INT UNSIGNED NULL,
  created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY (email), KEY (status), KEY (company_id), KEY (owner_id),
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL,
  FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE deals (
  id VARCHAR(24) PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  company_id VARCHAR(24) NULL, contact_id VARCHAR(24) NULL,
  stage_id INT UNSIGNED NOT NULL,
  amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  `close` DATE NULL,                              -- expected close date
  closed_at DATETIME NULL,                        -- set automatically when moved to Won/Lost
  owner_id INT UNSIGNED NULL,
  created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY (stage_id), KEY (company_id), KEY (contact_id), KEY (owner_id),
  FOREIGN KEY (stage_id) REFERENCES pipeline_stages(id),
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL,
  FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL,
  FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE activities (
  id VARCHAR(24) PRIMARY KEY,
  type VARCHAR(20) NOT NULL DEFAULT 'task',       -- call|email|meeting|note|task
  subject VARCHAR(255) NOT NULL,
  contact_id VARCHAR(24) NULL, deal_id VARCHAR(24) NULL,
  due DATE NULL, description TEXT,
  done TINYINT(1) NOT NULL DEFAULT 0,
  owner_id INT UNSIGNED NULL,
  created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY (contact_id), KEY (deal_id), KEY (owner_id, done, due),
  FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL,
  FOREIGN KEY (deal_id) REFERENCES deals(id) ON DELETE SET NULL,
  FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
