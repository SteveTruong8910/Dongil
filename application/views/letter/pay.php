<style>
    #hd, #ft_menu{ display: none; }
</style>

<script src="/assets/js/nicepay.js<?=$this->config->item('ver')?>"></script>
<script>
    let orderId = 'dongl-' + '<?=$writeInfo['idx']?>' + '-' + generateUniqueRandomString(15);
    
    AUTHNICE.requestPay({
        clientId: 'R2_b675488f4ff44cba95de01808d9054ad', /* 'S2_af4543a0be4d49a98122e01ec2059a56' */
        appScheme: `dongldn://`,
        method: '<?=$_GET['payType']?>',
        orderId: orderId,
        amount: '<?=$realTotalPrice?>',
        goodsName: '<?=$writeInfo['productName']?>',
        returnUrl: 'https://dongl.co.kr/payReturnUrl', //API를 호출할 Endpoint 입력
        fnError: function (result) {
            showAlert('결제가 취소되었습니다.', nav.goBack());
        }
    });
</script>