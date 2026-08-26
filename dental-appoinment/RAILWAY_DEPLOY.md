# นำ DentiCare ขึ้น Railway สำหรับส่งอาจารย์

## 1. นำโค้ดขึ้น GitHub

สร้าง Repository ใหม่บน GitHub ชื่อ `dental-appoinment` และตั้งเป็น Private หรือ Public จากนั้นเปิด Command Prompt ในโฟลเดอร์โปรเจกต์แล้วรัน:

```bat
cd /d C:\dental-appoinment
git init
git add .
git commit -m "Prepare DentiCare for Railway"
git branch -M main
git remote add origin https://github.com/ชื่อผู้ใช้/dental-appoinment.git
git push -u origin main
```

หากยังไม่มี Git ให้ติดตั้ง Git for Windows หรือใช้ GitHub Desktop เลือก Add existing repository แล้ว Publish repository

## 2. สร้าง Railway Project และ MySQL

1. เข้า https://railway.com และ Sign in ด้วย GitHub
2. กด New Project
3. กด Add Service → Database → MySQL
4. รอให้ MySQL พร้อมใช้งาน
5. กด Add Service → GitHub Repo แล้วเลือก Repository `dental-appoinment`

Railway จะพบ `Dockerfile` และสร้าง Web Service อัตโนมัติ

## 3. ตั้งค่าตัวแปร Web Service

เปิด Web Service → Variables และเพิ่ม:

```text
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_NAME=${{MySQL.MYSQLDATABASE}}
DB_USER=${{MySQL.MYSQLUSER}}
DB_PASS=${{MySQL.MYSQLPASSWORD}}
SETUP_KEY=denticare-demo-66011211023
```

คำว่า `MySQL` ต้องตรงกับชื่อ Database Service บน Railway หากตั้งชื่อบริการเป็นอย่างอื่น ให้เปลี่ยนชื่อใน `${{...}}` ให้ตรงกัน

## 4. สร้างลิงก์เว็บไซต์

1. เปิด Web Service → Settings
2. ไปที่ Networking → Public Networking
3. กด Generate Domain
4. เปิด URL ที่ Railway สร้างให้

## 5. สร้างตารางครั้งแรก

เปิด URL นี้หนึ่งครั้ง โดยแทน `ชื่อเว็บ` ด้วยโดเมน Railway:

```text
https://ชื่อเว็บ.up.railway.app/setup.php?key=denticare-demo-66011211023
```

เมื่อขึ้น “สร้างฐานข้อมูล DentiCare สำเร็จ” ให้กลับไปที่ Web Service → Variables แล้วลบ `SETUP_KEY` เพื่อปิดหน้าติดตั้ง

เข้าเว็บด้วยบัญชี:

```text
Username: admin
Password: 1234
```

## 6. เมื่อแก้โค้ดภายหลัง

```bat
git add .
git commit -m "Update DentiCare"
git push
```

Railway จะ Deploy เวอร์ชันใหม่ให้อัตโนมัติ โดยข้อมูลใน MySQL ไม่ถูกลบ

## หมายเหตุเรื่องแผนฟรี

Railway ให้เครดิตฟรีจำกัด เหมาะกับการสาธิตและส่งอาจารย์ ควรหยุดบริการหลังนำเสนอหากไม่ต้องการใช้เครดิตต่อ และห้ามเปิด Public TCP Proxy ของ MySQL หากไม่จำเป็น
