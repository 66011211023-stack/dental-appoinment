SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password_hash CHAR(64) NOT NULL,
  role ENUM('admin','staff','dentist','patient') NOT NULL,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(120),
  profile_image VARCHAR(255) NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE patients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  hn VARCHAR(20) UNIQUE NOT NULL,
  citizen_id VARCHAR(13),
  full_name VARCHAR(120) NOT NULL,
  birth_date DATE,
  phone VARCHAR(20),
  address TEXT,
  treatment_right VARCHAR(100),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE dentists (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  license_no VARCHAR(30) UNIQUE,
  specialty VARCHAR(100),
  phone VARCHAR(20),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  dentist_id INT NOT NULL,
  appointment_date DATE NOT NULL,
  appointment_time TIME NOT NULL,
  service VARCHAR(120) NOT NULL,
  status ENUM('pending','approved','completed','cancelled') DEFAULT 'pending',
  note TEXT,
  FOREIGN KEY (patient_id) REFERENCES patients(id),
  FOREIGN KEY (dentist_id) REFERENCES dentists(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE medical_histories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  allergy TEXT,
  disease TEXT,
  medicine TEXT,
  blood_pressure VARCHAR(20),
  recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE treatments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  appointment_id INT NOT NULL,
  diagnosis TEXT,
  treatment_detail TEXT,
  tooth_position VARCHAR(30),
  cost DECIMAL(10,2) DEFAULT 0,
  treated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (appointment_id) REFERENCES appointments(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE materials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  material_code VARCHAR(20) UNIQUE NOT NULL,
  material_name VARCHAR(120) NOT NULL,
  unit VARCHAR(30) NOT NULL,
  quantity INT DEFAULT 0,
  reorder_level INT DEFAULT 5,
  price DECIMAL(10,2) DEFAULT 0,
  expiry_date DATE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE material_transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  material_id INT NOT NULL,
  user_id INT NOT NULL,
  transaction_type ENUM('receive','issue') NOT NULL,
  quantity INT NOT NULL,
  note VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (material_id) REFERENCES materials(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE dentist_schedules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dentist_id INT NOT NULL,
  available_date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  status ENUM('available','reserved','unavailable') DEFAULT 'available',
  FOREIGN KEY (dentist_id) REFERENCES dentists(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE treatment_rights (
  id INT AUTO_INCREMENT PRIMARY KEY,
  right_code VARCHAR(20) UNIQUE NOT NULL,
  right_name VARCHAR(150) NOT NULL,
  organization VARCHAR(150),
  is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE login_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  logged_in_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(45),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users(username,password_hash,role,full_name,email) VALUES
('admin',SHA2('1234',256),'admin','สมชาย ผู้ดูแลระบบ','admin@denticare.local'),
('staff',SHA2('1234',256),'staff','อรทัย ใจดี','staff@denticare.local'),
('dentist',SHA2('1234',256),'dentist','ทพญ.ชลธิชา วัฒนะ','dentist@denticare.local'),
('patient',SHA2('1234',256),'patient','กิตติพงศ์ สุขใจ','patient@denticare.local');

INSERT INTO patients(user_id,hn,citizen_id,full_name,birth_date,phone,address,treatment_right) VALUES
((SELECT id FROM users WHERE username='patient'),'HN-00127','1234567890123','กิตติพงศ์ สุขใจ','1988-05-14','0812345678','มหาสารคาม','สิทธิข้าราชการ'),
(NULL,'HN-00128','2345678901234','พิมพ์ชนก แสงทอง','1995-09-21','0891112233','ร้อยเอ็ด','บัตรทอง');

INSERT INTO dentists(user_id,license_no,specialty,phone) VALUES
((SELECT id FROM users WHERE username='dentist'),'DT-003','ทันตกรรมทั่วไป','0822223344');

INSERT INTO appointments(patient_id,dentist_id,appointment_date,appointment_time,service,status,note) VALUES
(1,1,CURDATE() + INTERVAL 1 DAY,'10:30:00','ขูดหินปูน','approved','มาตรงเวลา 10 นาที'),
(2,1,CURDATE() + INTERVAL 2 DAY,'13:00:00','อุดฟัน','pending','ตรวจสอบคำขอ');

INSERT INTO medical_histories(patient_id,allergy,disease,medicine,blood_pressure) VALUES
(1,'ไม่แพ้ยา','ไม่มีโรคประจำตัว','ไม่มี','118/76');

INSERT INTO materials(material_code,material_name,unit,quantity,reorder_level,price,expiry_date) VALUES
('MAT-001','ถุงมือยาง','กล่อง',24,10,180,'2028-12-31'),
('MAT-002','ยาชา Lidocaine','กล่อง',6,8,950,'2027-06-30'),
('MAT-003','วัสดุอุดฟัน Composite','หลอด',14,5,720,'2027-11-30');

INSERT INTO treatment_rights(right_code,right_name,organization) VALUES
('UC','สิทธิหลักประกันสุขภาพแห่งชาติ','สำนักงานหลักประกันสุขภาพแห่งชาติ'),
('SSO','สิทธิประกันสังคม','สำนักงานประกันสังคม'),
('CSMBS','สิทธิเบิกจ่ายตรงข้าราชการ','กรมบัญชีกลาง'),
('CASH','ชำระเงินเอง','ผู้ป่วย');

INSERT INTO dentist_schedules(dentist_id,available_date,start_time,end_time,status) VALUES
(1,CURDATE() + INTERVAL 1 DAY,'09:00:00','09:30:00','available'),
(1,CURDATE() + INTERVAL 1 DAY,'10:30:00','11:00:00','reserved'),
(1,CURDATE() + INTERVAL 1 DAY,'13:00:00','14:30:00','available');
