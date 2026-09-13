    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center text-xs text-slate-500 gap-2">
            <p>&copy; <?= date('Y') ?> <strong>MEMORion+</strong> (Vixies Studio). Sistem Backend & Tracking Progres.</p>
            <div class="flex items-center space-x-4">
                <span>PHP Native</span>
                <span>&bull;</span>
                <span>MySQL Database</span>
                <span>&bull;</span>
                <a href="https://github.com/enkinq/Database-Memorion" target="_blank" class="hover:text-brand-600 font-medium text-slate-600 transition-colors">GitHub Repository</a>
            </div>
        </div>
    </footer>

    <!-- Copy to Clipboard & UI Helper Script -->
    <script>
        function copyToClipboard(text, elementId = null) {
            navigator.clipboard.writeText(text).then(() => {
                if (elementId) {
                    const el = document.getElementById(elementId);
                    const originalText = el.innerText;
                    el.innerText = 'Tersalin!';
                    el.classList.add('text-emerald-600');
                    setTimeout(() => {
                        el.innerText = originalText;
                        el.classList.remove('text-emerald-600');
                    }, 2000);
                } else {
                    alert('API Key berhasil disalin ke clipboard!');
                }
            }).catch(err => {
                console.error('Gagal menyalin text: ', err);
            });
        }
    </script>
</body>
</html>
