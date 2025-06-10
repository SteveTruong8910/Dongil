<style>
    #hd, #ft_menu{ display: none; }
</style>

<script src="/assets/js/nicepay.js<?=$this->config->item('ver')?>"></script>
<script>
    let orderId = 'dlpoint-' +  '<?=$_GET['memberIdx']?>' + '-' + generateUniqueRandomString(15);    
    
    AUTHNICE.requestPay({
        clientId: 'R2_b675488f4ff44cba95de01808d9054ad', /* 'S2_af4543a0be4d49a98122e01ec2059a56' */
        appScheme: `dongldn://`,
        method: '<?=$_GET['payType']?>',
        orderId: orderId,
        amount: '<?=$_GET['amount']?>',
        goodsName: '동글포인트',
        returnUrl: 'https://dongl.co.kr/payPointReturnUrl', //API를 호출할 Endpoint 입력
        fnError: function (result) {
            showAlert('결제가 취소되었습니다.', nav.goBack());
        }
    });
</script>