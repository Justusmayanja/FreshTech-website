      </section>
    </main>
  </div>
  <script src="/admin/assets/js/admin.js"></script>
</body>
</html>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-12">
        <div class="max-w-7xl mx-auto px-6 py-6 text-center text-gray-600 text-sm md:ml-64">
            <p>&copy; 2025 FreshTech Solutions. All rights reserved.</p>
        </div>
    </footer>

    <!-- Flowbite JS -->
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.4.1/dist/flowbite.min.js"></script>

    <script>
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const toggleBtn = document.getElementById('toggleSidebar');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('hidden');
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            });
        }

        // Close sidebar on Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            }
        });

        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');
                const icon = themeToggle.querySelector('i');
                if (document.documentElement.classList.contains('dark')) {
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                    localStorage.setItem('theme', 'dark');
                } else {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                    localStorage.setItem('theme', 'light');
                }
            });

            // Apply saved theme
            const savedTheme = localStorage.getItem('theme') || 'light';
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
                const icon = themeToggle.querySelector('i');
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        }

        // Notification toast (for flash messages)
        function showNotification(message, type = 'success') {
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            const toast = document.createElement('div');
            toast.className = `fixed top-6 right-6 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg z-50 animate-pulse`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
    </script>

    <?php if (isset($notification)): ?>
        <script>
            showNotification('<?php echo sanitize($notification['message']); ?>', '<?php echo sanitize($notification['type']); ?>');
        </script>
    <?php endif; ?>
</body>
</html>
