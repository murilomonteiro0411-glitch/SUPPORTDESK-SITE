<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireLogin();
if (isAdmin()) { header('Location: admin/dashboard.php'); exit; }

$code = $_GET['code'] ?? '';
$plan = $_GET['plan'] ?? 'Plano Support Desk';
$amount = $_GET['amount'] ?? '59.90';

// Buscar o pagamento pendente no banco
$stmt = $pdo->prepare("SELECT * FROM payments WHERE transaction_code = ? AND user_id = ? LIMIT 1");
$stmt->execute([$code, $_SESSION['user_id']]);
$payment = $stmt->fetch();

if (!$payment) {
    header('Location: plans.php');
    exit;
}

// Chave PIX copia e cola fictícia formatada
$pixKey = "00020126580014BR.GOV.BCB.PIX0136".md5($code)."5204000053039865405".number_format($amount, 2, '.', '')."5802BR5920SUPPORT DESK LTDA6009SAO PAULO62070503***63041D2C";

$pageTitle = 'Pagamento via PIX';
$basePath = '';
require_once 'includes/header.php';
?>

<main class="dashboard-page pix-checkout-page">
    <div class="container">
        <div class="pix-card-wrapper">
            <div class="pix-card-header">
                <div class="pix-pulse-badge"><span class="pulse"></span> Aguardando Confirmação do PIX</div>
                <h1>Escaneie o QR Code para pagar</h1>
                <p>Abra o app do seu banco e escolha a opção <strong>PIX -> Ler QR Code</strong> ou use o código Copia e Cola.</p>
            </div>

            <div class="pix-card-body">
                <div class="pix-qr-container">
                    <!-- Gerador de QR Code limpo via API de SVG/Image segura -->
                    <div class="qr-frame">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=<?=urlencode($pixKey)?>" alt="QR Code PIX" class="qr-img">
                    </div>
                    <div class="qr-timer">⏱️ Código válido por <strong>30:00 minutos</strong></div>
                </div>

                <div class="pix-details-container">
                    <div class="pix-summary-row">
                        <span>Plano Escolhido:</span>
                        <strong><?=htmlspecialchars($plan)?></strong>
                    </div>
                    <div class="pix-summary-row">
                        <span>Valor Total:</span>
                        <strong class="pix-amount">R$ <?=number_format((float)$amount, 2, ',', '.')?></strong>
                    </div>
                    <div class="pix-summary-row">
                        <span>Código do Pedido:</span>
                        <code><?=htmlspecialchars($code)?></code>
                    </div>

                    <div class="pix-copy-box">
                        <label>Chave PIX Copia e Cola:</label>
                        <div class="copy-input-group">
                            <input type="text" value="<?=htmlspecialchars($pixKey)?>" id="pixInput" readonly>
                            <button class="btn btn-primary" onclick="copyPix()">Copiar Código</button>
                        </div>
                        <span id="copyFeedback" class="copy-feedback">Código copiado com sucesso!</span>
                    </div>

                    <div class="pix-status-alert">
                        <div class="spinner"></div>
                        <div>
                            <strong>Verificando pagamento em tempo real...</strong>
                            <p>Assim que o administrador aprovar o comprovante, seu painel será liberado automaticamente.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pix-card-footer">
                <a href="user/dashboard.php" class="btn btn-glass">Ir para meu painel (Status: Pendente)</a>
            </div>
        </div>
    </div>
</main>

<style>
.pix-checkout-page { padding: 50px 0; }
.pix-card-wrapper { max-width: 860px; margin: 0 auto; background: linear-gradient(160deg, #0b1c32 0%, #06101f 100%); border: 1px solid rgba(130, 188, 255, 0.18); border-radius: 24px; padding: 40px; box-shadow: 0 30px 80px rgba(0,0,0,0.4); }
.pix-card-header { text-align: center; margin-bottom: 35px; }
.pix-pulse-badge { display: inline-flex; align-items: center; gap: 8px; padding: 6px 16px; background: rgba(255, 204, 76, 0.12); border: 1px solid rgba(255, 204, 76, 0.3); color: #ffd36c; border-radius: 99px; font-size: 12px; font-weight: 700; margin-bottom: 14px; }
.pix-card-header h1 { font-family: 'Space Grotesk', sans-serif; font-size: 32px; font-weight: 700; color: #fff; margin-bottom: 8px; }
.pix-card-header p { font-size: 14px; color: #91a8c3; max-width: 580px; margin: 0 auto; line-height: 1.5; }

.pix-card-body { display: grid; grid-template-columns: 280px 1fr; gap: 35px; align-items: start; background: rgba(14, 37, 65, 0.4); border: 1px solid rgba(130, 188, 255, 0.12); border-radius: 20px; padding: 28px; }
.pix-qr-container { text-align: center; }
.qr-frame { background: #fff; padding: 14px; border-radius: 16px; display: inline-block; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
.qr-img { width: 100%; height: auto; display: block; }
.qr-timer { margin-top: 14px; font-size: 12px; color: #8fa8c5; background: rgba(8, 24, 43, 0.8); padding: 8px 12px; border-radius: 10px; border: 1px solid rgba(130, 188, 255, 0.1); }

.pix-details-container { display: flex; flex-direction: column; gap: 14px; }
.pix-summary-row { display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid rgba(130, 188, 255, 0.1); font-size: 14px; color: #a0b6d0; }
.pix-amount { font-size: 20px; color: #43d7ff; font-weight: 800; }
.pix-summary-row code { background: #08182b; color: #62b0ff; padding: 4px 8px; border-radius: 6px; font-size: 12px; }

.pix-copy-box { margin-top: 10px; }
.pix-copy-box label { display: block; font-size: 12px; font-weight: 600; color: #a9c4e2; margin-bottom: 6px; }
.copy-input-group { display: flex; gap: 10px; }
.copy-input-group input { flex: 1; background: #08182b; border: 1px solid #173557; border-radius: 10px; color: #fff; padding: 10px 14px; font-size: 12px; outline: none; }
.copy-feedback { display: none; font-size: 11px; color: #50d6a0; margin-top: 6px; font-weight: 600; }

.pix-status-alert { display: flex; gap: 14px; align-items: center; padding: 14px 16px; background: rgba(46, 140, 255, 0.08); border: 1px solid rgba(46, 140, 255, 0.2); border-radius: 12px; margin-top: 10px; }
.spinner { width: 22px; height: 22px; border: 3px solid rgba(67, 215, 255, 0.2); border-top-color: #43d7ff; border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.pix-status-alert strong { font-size: 12px; color: #fff; display: block; }
.pix-status-alert p { font-size: 11px; color: #8fa8c5; margin: 2px 0 0; line-height: 1.4; }

.pix-card-footer { text-align: center; margin-top: 28px; }

@media (max-width: 768px) {
    .pix-card-body { grid-template-columns: 1fr; }
}
</style>

<script>
function copyPix() {
    const input = document.getElementById('pixInput');
    input.select();
    navigator.clipboard.writeText(input.value);
    
    const fb = document.getElementById('copyFeedback');
    fb.style.display = 'block';
    setTimeout(() => fb.style.display = 'none', 3000);
}

// Auto-check for approval every 5 seconds
setInterval(async () => {
    try {
        const res = await fetch('check-payment-status.php?code=<?=urlencode($code)?>');
        const data = await res.json();
        if (data.status === 'Aprovado') {
            window.location.href = 'payment-success.php?code=<?=urlencode($code)?>&plan=<?=urlencode($plan)?>';
        }
    } catch(e) {}
}, 5000);
</script>

<?php require_once 'includes/footer.php'; ?>
