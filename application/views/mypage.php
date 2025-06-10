<div id="mypage">    
    <p class="ment">
        <?=!empty($this->user['nickname'])? $this->user['nickname'] : '회원'?>님 안녕하세요!🙇
    </p>        
<!--
    <div class="pointInfo">
        <img src="/assets/image/ico/ico_coin.png" style="width: 20px; margin-right: 5px;">
        <span class="pointName"> 동글 포인트</span>
        <span class="point"><?=number_format($this->user['point'])?> 원</span>
    </div>
-->
    
    <p class="serviceMent">서비스</p>
    <div class="service">        
        <a href="javascript:nav.locationHref('/mypage/point', 'd1')" class="box">
            <span>포인트 관리</span>
            <span><img class="arrow" src="/assets/image/ico/ico_arrow_right.png"></span>
        </a>        
        
        <a href="javascript:nav.locationHref('/mypage/myLetter', 'b4')" class="box">
            <span>내가 쓴 편지</span>
            <span><img class="arrow" src="/assets/image/ico/ico_arrow_right.png"></span>
        </a>

        <a href="javascript:nav.locationHref('/mypage/myMailbox', 'b4')" class="box">
            <span>사서함 편지</span>
            <span><img class="arrow" src="/assets/image/ico/ico_arrow_right.png"></span>
        </a>

        <!-- <a href="javascript:nav.locationHref('/mypage/myDocument', 'b4')" class="box">
            <span>개인 문서함</span>
            <span><img class="arrow" src="/assets/image/ico/ico_arrow_right.png"></span>
        </a> -->
        
        <a href="javascript:nav.locationHref('/mypage/tmpLetter', 'b6')" class="box">
            <span>임시저장된 편지</span>
            <span><img class="arrow" src="/assets/image/ico/ico_arrow_right.png"></span>
        </a>
                
        <a href="javascript:nav.locationHref('/mypage/myAddress', 'b4')" class="box">
            <span>주소지 관리</span>
            <span><img class="arrow" src="/assets/image/ico/ico_arrow_right.png"></span>
        </a>
        
        <a href="javascript:nav.locationHref('/board?type=qna', 'c3')" class="box">
            <span>1대1 문의하기</span>
            <span><img class="arrow" src="/assets/image/ico/ico_arrow_right.png"></span>
        </a>
<!--
        <a class="box">
            <span>공지사항</span>
            <span><img class="arrow" src="/assets/image/ico/ico_arrow_right.png"></span>
        </a>
-->
        <div style="display: flex; justify-content: space-between; align-items: center;">            
            <a class="removeBtn" onclick="removeMember()">회원탈퇴</a>            
            <a class="btnLogout" href="/logout">로그아웃</a>                                    
        </div>    
    </div>
</div>

<script>
    
    // 회원 탈퇴
    function removeMember(){
        showConfirm('회원님 동글을 떠나신다니 아쉬워요😢<br>정말 고민 많이 해보신 거 맞으시죠!?<br>만약 다음에 찾아오셔서 계정복구를 원하시면 다시 고객센터로 문의주세요!')
        .then(async function (result){
            if(!result.value) return;
            
            showAlert('동글 서비스를 이용해주셔서 정말 감사했어요! 저희는 회원님이 다시 돌아올 거라고 믿어요! 앞으로 하시는 일 모두 잘 되길 동글이 빌게요🙇‍♀️')
            .then(async () => {                        
                const removeMemberRes = await postJson('/pointApi/removeMember', {});

                if(!removeMemberRes.result){
                    showAlert(removeMemberRes.msg);
                    return;
                }
                        
                nav.locationHref('/', 'clear');
            });
        });
    }
</script>