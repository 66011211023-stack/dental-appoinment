/** JavaScript ฝั่งหน้าจอ: Toast, เมนูมือถือ, ค้นหา, ลืมรหัสผ่าน และขั้นตอนจองคิว */
const toast = document.querySelector('#toast');
function notify(message) {
  if (!toast) return;
  toast.querySelector('span').textContent = message;
  toast.hidden = false;
  clearTimeout(window.toastTimer);
  window.toastTimer = setTimeout(() => { toast.hidden = true; }, 2600);
}

document.querySelectorAll('[data-toast]').forEach((element) => element.addEventListener('click', (event) => {
  if (element.getAttribute('href') === '#') event.preventDefault();
  notify(element.dataset.toast);
}));

const sidebar = document.querySelector('#sidebar');
const backdrop = document.querySelector('#navBackdrop');
document.querySelector('#menuButton')?.addEventListener('click', () => sidebar?.classList.add('open'));
backdrop?.addEventListener('click', () => sidebar?.classList.remove('open'));
sidebar?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => sidebar.classList.remove('open')));
document.addEventListener('keydown', (event) => { if (event.key === 'Escape') sidebar?.classList.remove('open'); });
window.addEventListener('resize', () => { if (window.innerWidth > 1024) sidebar?.classList.remove('open'); });

document.querySelector('#search')?.addEventListener('input', function () {
  const query = this.value.toLowerCase();
  document.querySelectorAll('#dataTable tbody tr').forEach((row) => { row.hidden = !row.innerText.toLowerCase().includes(query); });
});

function showAuth(name) {
  document.querySelectorAll('.auth-screen').forEach((screen) => { screen.hidden = screen.dataset.auth !== name; });
}
document.querySelectorAll('.auth-go').forEach((button) => button.addEventListener('click', () => showAuth(button.dataset.target)));

let bookingStep = 1;
const bookingPanes = document.querySelectorAll('.booking-pane');
const bookingIndicators = document.querySelectorAll('.booking-steps span');
const bookingBack = document.querySelector('#bookingBack');
const bookingNext = document.querySelector('#bookingNext');
function renderBooking() {
  bookingPanes.forEach((pane) => { pane.hidden = Number(pane.dataset.step) !== bookingStep; });
  bookingIndicators.forEach((item, index) => item.classList.toggle('active', index < bookingStep));
  if (bookingBack) bookingBack.disabled = bookingStep === 1;
  if (bookingNext) bookingNext.textContent = bookingStep < 3 ? 'ถัดไป' : 'ยืนยันการจอง';
}
bookingBack?.addEventListener('click', () => { if (bookingStep > 1) bookingStep--; renderBooking(); });
bookingNext?.addEventListener('click', () => { if (bookingStep < 3) bookingStep++; else notify('ส่งคำขอจองคิวเรียบร้อยแล้ว'); renderBooking(); });

document.querySelectorAll('.tabs button').forEach((button) => button.addEventListener('click', () => {
  button.parentElement.querySelectorAll('button').forEach((item) => item.classList.remove('active'));
  button.classList.add('active');
}));

// ปุ่มรูปตาสำหรับแสดงหรือซ่อนรหัสผ่านในหน้า Login และ Register
document.querySelectorAll('[data-password-toggle]').forEach((button) => {
  button.addEventListener('click', () => {
    const wrapper = button.closest('.password-input-wrap');
    const input = wrapper?.querySelector('input');
    if (!input) return;

    const showing = input.type === 'text';
    input.type = showing ? 'password' : 'text';
    button.classList.toggle('showing', !showing);
    button.setAttribute('aria-label', showing ? 'แสดงรหัสผ่าน' : 'ซ่อนรหัสผ่าน');
    button.setAttribute('title', showing ? 'แสดงรหัสผ่าน' : 'ซ่อนรหัสผ่าน');
  });
});

// แสดงตัวอย่างรูปโปรไฟล์ก่อนกดบันทึก
document.querySelector('#profileImageInput')?.addEventListener('change', function () {
  const file = this.files?.[0];
  const preview = document.querySelector('#profilePreview');
  if (!file || !preview) return;
  if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024) {
    this.value = '';
    notify('กรุณาเลือกรูป JPG, PNG หรือ WEBP ขนาดไม่เกิน 2 MB');
    return;
  }
  const image = document.createElement('img');
  image.alt = 'ตัวอย่างรูปโปรไฟล์';
  image.src = URL.createObjectURL(file);
  image.addEventListener('load', () => URL.revokeObjectURL(image.src), { once: true });
  preview.replaceChildren(image);
});
