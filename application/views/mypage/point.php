<style>
    #ft_menu {display: none;}
</style>
<link rel="stylesheet" type="text/css" href="/assets/css/view/point.css<?=$this->config->item('ver')?>">

<div id="point" style="padding: 0 20px;">
    <div class="pointBox">
        <p class="myPointTxt">보유 포인트</p>
        <p class="myPoint"><?=number_format($this->user['point'])?>P</p>
        
        <!-- 포인트 충전 일시 중단 -->
        <!-- <a href="/mypage/addPoint">충전하기</a> -->
    </div>
    <p class="line"></p>
    
    <div class="pointListBox">
        <div class="pointCateWrap">
            <span class="pointCate active" onclick="changeCate($(this), 'all')">전체</span>
            <span class="pointCate" onclick="changeCate($(this), 'plus')">충전</span>
            <span class="pointCate" onclick="changeCate($(this), 'minus')">사용</span>
        </div>
        
        <div id="pointList">            
        </div>
    </div>
</div>

<script>
    var page = 1,  // 현재 페이지 번호
        type = 'all',  // 현재 선택된 카테고리 (기본값: 'all')
        isLoading = false,  // 중복 호출 방지 플래그
        isFinish = false;  // 데이터가 모두 로딩되었는지 확인하는 플래그
    
    // 기본 설정 함수, 페이지 로드 시 호출되어 포인트 리스트를 가져오고, 무한 스크롤을 설정
    function defaultSetup() {
        getPointList();  // 포인트 내역을 가져오는 함수 호출
        setupInfiniteScroll();  // 무한 스크롤 설정 함수 호출
    }
    
    // 카테고리 변경 시 호출되는 함수
    function changeCate($this, changeType) {
        $('.pointCate').removeClass('active');  // 모든 카테고리에서 active 클래스 제거
        $this.addClass('active');  // 클릭한 카테고리에 active 클래스 추가
        
        type = changeType;  // 선택된 카테고리 타입 업데이트
        page = 1;  // 페이지 번호 초기화
        getPointList();  // 포인트 내역을 새로 가져오기
    }
    
    // 포인트 내역을 가져오는 비동기 함수
    async function getPointList() {
        if (isLoading) return;  // 이미 로딩 중이면 함수 종료
        isLoading = true;  // 로딩 시작
        
        // 서버에 포인트 리스트 요청
        const getPointListRes = await postJson('/pointApi/getPointList', {
            page: page,
            type: type
        });
        
        let $pointList = $('#pointList'),  // 포인트 리스트가 표시될 DOM 요소
            list = getPointListRes.list,  // 서버에서 받은 포인트 내역 데이터
            pointLog = '';  // 포인트 내역을 담을 변수
        
        if (page == 1) {
            $pointList.empty();  // 첫 페이지 로딩 시 기존 리스트 초기화
        }
        
        // 포인트 내역이 없을 경우
        if (!list.length && page == 1) {
            pointLog = `<div class="empty">포인트 내역이 존재하지 않아요!</div>`;  // 내역 없음 메시지 표시
        } else if (!list.length) {
            // 추가 데이터가 없을 경우 로딩 중지
            isLoading = false;
            return;
        }
        
        // 리스트 길이가 50보다 적으면 데이터가 더 이상 없다고 판단
        if (list.length < 50) {
            isFinish = true;
        }
        
        // 리스트 항목 반복하여 HTML로 만들어 추가
        for (let data of list) {
            let point = data.isWait == 'Y' ? (data.point - data.bonus) : data.point,  // 대기 중인 포인트 계산
                clsPoint = Number(data.point) > 0 ? 'plus' : 'minus',  // 포인트가 양수이면 'plus', 아니면 'minus'
                wait = '';  // 입금 대기 상태 여부
            
            if (data.isWait == 'Y') {
                wait = `<span class="wait">입금대기</span>`;  // 입금 대기 상태인 경우 표시
            }
            
            // 포인트 로그 항목 HTML 생성
            pointLog += `
                <div class="pointLog">
                    <div class="logLeft">                    
                        <div class="date">${data.dateMD}</div>
                        <div class="detail">
                            <p class="d1">
                                ${data.ment}
                                ${wait}  <!-- 입금 대기 상태 표시 -->
                            </p>
                            <p class="d2">
                                <span>${data.dateHI} | 포인트 ${clsPoint == 'plus' ? '충전' : '사용'}</span>
                            </p>
                        </div>
                    </div>
                    <div class="logRight">
                        <span class="point ${clsPoint}">${clsPoint == 'plus' ? '+' : ''}${comma(point)}원</span>
                    </div>
                </div>
            `;
        }
        
        $pointList.append(pointLog);  // 포인트 내역을 DOM에 추가
        isLoading = false;  // 로딩 완료
    }
    
    // 무한 스크롤을 위한 함수
    function setupInfiniteScroll() {
        $(window).on('scroll', function () {
            // 현재 스크롤 위치와 문서의 높이를 계산하여 바닥에 닿았는지 확인
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 100) {
                if (!isFinish && !isLoading) {
                    page++;  // 페이지 번호 증가
                    getPointList();  // 포인트 내역을 가져오는 함수 호출
                }
            }
        });
    }
    
    defaultSetup();  // 페이지 로드 시 기본 설정 함수 호출
</script>