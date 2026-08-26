-- รันครั้งเดียวใน phpMyAdmin สำหรับฐานข้อมูลเดิม
ALTER TABLE users
ADD COLUMN profile_image VARCHAR(255) NULL AFTER email;
