<?php
$orderId = $_GET['orderId'];
$payType = $_GET['payType'];
$realTotalPrice = (int)$_GET['amount'];
$productName = urldecode($_GET['productName']);
?>

<style>
    #hd, #ft_menu { display: none; }
</style>

<script src="/assets/js/nicepay.js<?=$this->config->item('ver')?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    try {
        AUTHNICE.requestPay({
            clientId: 'R2_b675488f4ff44cba95de01808d9054ad',
            appScheme: 'dongldn://',
            method: '<?= $payType ?>',
            orderId: '<?= $orderId ?>',
            amount: <?= $realTotalPrice ?>,
            goodsName: '<?= addslashes($productName) ?>',
            returnUrl: 'https://dongl.co.kr/payReturnUrl',
            fnError: function (result) {
                console.log("결제 실패:", result);
                alert('결제가 취소되었습니다.');
                location.replace('/mypage/myMailbox');
            }
        });
    } catch (e) {
        console.error("AUTHNICE 호출 오류:", e);
        alert('결제 요청 중 오류가 발생했습니다.');
        location.replace('/mypage/myMailbox');
    }
});
</script>
