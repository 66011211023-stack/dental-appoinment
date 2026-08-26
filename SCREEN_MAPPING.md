# ตารางเทียบเว็บไซต์ต้นแบบกับโค้ด PHP

ไฟล์นี้ใช้ตรวจว่าหน้าจอจากเว็บไซต์ต้นแบบถูกนำมาไว้ที่ไฟล์ใดในโปรเจกต์ PHP

| URL/เมนูต้นแบบ | ไฟล์ PHP | หน้าที่ |
|---|---|---|
| Login / Forgot password | `app/Views/auth/login.php` | เข้าสู่ระบบและกระบวนการตั้งรหัสผ่านใหม่ |
| Dashboard | `app/Views/dashboard/index.php` | แดชบอร์ดแยกตามผู้ใช้ 4 กลุ่ม |
| Appointments | `app/Views/modules/appointments.php` | คำขอ นัดหมาย และนัดหมายของผู้ป่วย |
| Schedule | `app/Views/modules/schedule.php` | ตารางเวลาว่างและตารางรักษา |
| Patients | `app/Views/modules/patients.php` | ข้อมูลผู้ป่วย ซักประวัติ และประวัติรักษาในหน้าเดียว |
| Treatments | `app/Views/modules/treatments.php` | วินิจฉัย หัตถการ ผังฟัน และค่าใช้จ่าย |
| Inventory | `app/Views/modules/materials.php` | รับ เบิก และตรวจวัสดุคงเหลือ |
| Staff | `app/Views/modules/users.php` | บุคลากรและบัญชีผู้ใช้ |
| Rights | `app/Views/modules/rights.php` | สิทธิ์การรักษา |
| Reports | `app/Views/modules/reports.php` | รายงานและสถิติของผู้ดูแล/เจ้าหน้าที่ |
| Booking | `app/Views/modules/booking.php` | จองคิวแบบ 3 ขั้นตอน |
| Treatment history | `app/Views/modules/history.php` | ประวัติการรักษาของผู้ป่วย |
| Notifications | `app/Views/modules/notifications.php` | การแจ้งเตือนของผู้ป่วย |
| Profile | `app/Views/modules/profile.php` | ข้อมูลส่วนตัว |
| Material usage | `app/Views/modules/material-usage.php` | ทันตแพทย์เบิกวัสดุ |
| Service statistics | `app/Views/modules/service-stats.php` | สถิติการให้บริการของทันตแพทย์ |

## ไฟล์ที่ควบคุมหน้าตาทั้งระบบ

- `public/assets/css/browser-latest.css` คือ CSS จากเว็บไซต์ต้นแบบ
- `public/assets/css/php-compat.css` ทำให้ลิงก์ PHP แสดงเหมือนปุ่มของต้นแบบ
- `app/Views/layouts/header.php` คือ Sidebar และ Topbar ที่ใช้ร่วมกัน
- `app/Views/components.php` คือหัวข้อ การ์ดสถิติ และตารางที่ใช้ซ้ำ
- `public/assets/js/app.js` ควบคุม Toast เมนูมือถือ ค้นหา และขั้นตอนจองคิว
