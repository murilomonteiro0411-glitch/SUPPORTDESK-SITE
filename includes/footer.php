<footer id="contato" class="footer">
    <div class="container footer-grid">
        <div>
            <a class="logo footer-logo" href="#">
                <span class="logo-icon">S</span>
                <span>Support<span>Desk</span></span>
            </a>
            <p>Suporte técnico rápido, organizado e humanizado para pessoas e empresas.</p>
        </div>
        <div>
            <h4>Atendimento</h4>
            <a href="mailto:contato@supportdesk.com">contato@supportdesk.com</a>
            <span>Segunda a sábado</span>
            <span>08:00 às 20:00</span>
        </div>
        <div>
            <h4>Links</h4>
            <a href="#servicos">Serviços</a>
            <a href="#como-funciona">Como funciona</a>
            <a href="<?= $basePath ?? '' ?>auth/login.php">Área do cliente</a>
        </div>
    </div>
    <div class="container footer-bottom">© <?= date('Y') ?> Support Desk. Projeto acadêmico.</div>
</footer>
<script src="<?= $basePath ?? '' ?>assets/js/app.js"></script>
</body>
</html>
