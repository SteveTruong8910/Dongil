<style>
    #ft_menu { display: none; }
</style>

<div id="board">
    <div class="cate">
    <? foreach($this->config->item('noticeCate') as $key => $name){ ?>
        <a href="/board?type=<?=$key?>" class="<?=$key == $type? 'active': ''?>"><?=$name?></a>        
    <? } ?>
    </div>
    
    <? if($type != 'review'){ ?>
        <div class="searchBox">
            <input type="text" id="searchText" placeholder="제목을 입력해주세요." onkeyup="getBoardList();">
            <i class="fas fa-search"></i>
        </div>
    <? } ?>    
    
    <? if($type == 'qna'){ ?>
        <a href="http://pf.kakao.com/_AxlNCn/chat" target="_blank" class="writeBoard" style="border: 1px solid #cbcb00; background: #ffff9d; color: #747474;">
            <img src="/assets/image/ico/ico_kakao_login.png" width="25" style="margin-right: 5px;"> 카카오톡으로 문의하기
        </a>
        <a href="javascript:nav.locationHref('/board/write', 'c3')" class="writeBoard">
            <i class="fas fa-pencil-alt" style="margin-right: 5px;"></i> 문의 등록
        </a>
    <? } ?>
    
    <div id="boardList" class="boardList">
    </div>          
</div>

<script>
    // 사용자가 로그인했는지 여부를 확인 (세션에 사용자 정보가 있으면 'Y', 없으면 'N')
    const isLogin = "<?=!empty($this->user)? 'Y' : 'N'?>";
    // 현재 페이지에서 사용되는 'type' 값을 PHP에서 받아옴
    const type = '<?=$type?>';        

    // 게시판 목록을 가져오는 함수
    async function getBoardList(isInit = false){
        let list = '';
        
        // 게시판 목록을 API 호출을 통해 가져옴
        const getBoardListRes = await postJson('/userApi/getBoardList', {
            type : type,  // 게시판 유형
            searchText: $('#searchText').val()  // 검색 텍스트
        }, isInit);

        // API 호출이 실패했을 경우 메시지 출력
        if(!getBoardListRes.result){
            showAlert(getBoardListRes.msg);
            return;
        }

        // 게시글이 없을 경우 빈 메시지를 출력
        if(!getBoardListRes.list.length){
            list = '<div class="empty">등록된 글이 없습니다</div>';
        }

        // 게시판에 등록된 글들을 순차적으로 HTML로 추가
        for(let i=0; i<getBoardListRes.list.length; i++){
            let data = getBoardListRes.list[i],
                answerBox = '',
                evnet = (type != 'qna' || isLogin == 'Y')? `href="javascript:nav.locationHref('/board/view/${data.idx}?type=${type}', 'c2')"` : 
                                                           `onclick="checkBoardPwd(${data.idx})"`;  // 게시글 보기 버튼을 클릭했을 때, 로그인 상태나 타입에 따라 이벤트 변경
            
            // 문의 게시판의 경우 답변 여부 표시
            if(data.type == '문의'){
                if(data.answerIdx != '0'){
                    answerBox = `<span class="answerBox active">답변</span>`;
                }else{
                    answerBox = `<span class="answerBox">미답변</span>`;
                }
            }

            // 게시글 리스트에 HTML을 추가
            list += `<a ${evnet} class="boardTab">
                        <div class="boardCate">${data.type}</div>
                        <div class="content">
                            <p>${data.regDate} ${answerBox}</p>
                            <p>${data.title}</p>
                        </div>
                    </a>`;
        }
        
        // 게시글 목록을 HTML로 출력
        $('#boardList').html(list);
    }

    // 게시글의 비밀번호를 확인하는 함수
    async function checkBoardPwd(idx){
        // 비밀번호 입력을 유도하는 프롬프트
        const boardPwd = prompt("비밀번호를 입력하세요.");

        // 비밀번호가 입력되지 않으면 종료
        if(!boardPwd) return;

        // 입력한 비밀번호로 게시글의 비밀번호를 체크
        const checkBoardPwdRes = await postJson('/userApi/checkBoardPwd', {
            boardIdx : idx,  // 게시글 ID
            boardPwd : boardPwd  // 입력한 비밀번호
        });

        // 비밀번호가 맞지 않으면 메시지 출력
        if(!checkBoardPwdRes.result){
            showAlert(checkBoardPwdRes.msg);
            return;
        }

        // 비밀번호가 맞으면 게시글 보기 페이지로 이동
        nav.locationHref(`/board/view/${idx}?type=${type}`, 'c2');
    }

    $(function(){
        // 페이지 로딩 시 게시글 목록을 불러옴
        getBoardList(false); 
    });
</script>