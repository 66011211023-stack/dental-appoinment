</main>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. ปุ่มสลับหน้า Login / Forgot Password / New Password
        document.querySelectorAll('.auth-go').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const target = btn.dataset.target;
                document.querySelectorAll('.auth-screen').forEach(el => el.hidden = true);
                const targetEl = document.querySelector(`.auth-screen[data-auth="${target}"]`);
                if (targetEl) targetEl.hidden = false;
            });
        });

        // 2. ปุ่มเปิด/ปิดตาแสดงรหัสผ่าน
        document.querySelectorAll('[data-password-toggle]').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.previousElementSibling;
                if (input.type === 'password') {
                    input.type = 'text';
                    btn.classList.add('is-visible');
                } else {
                    input.type = 'password';
                    btn.classList.remove('is-visible');
                }
            });
        });
    });
    </script>
</body>
</html>
