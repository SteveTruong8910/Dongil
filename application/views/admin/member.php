<p class="title">회원 관리</p>

<div id="notice">    
    <div class="local">
        <!-- 총 회원 수 표시 -->
        총 회원 수 <?=$pageData['totalCnt']?>명
    </div>

    <!-- 회원 검색 폼 -->
    <form id="searchForm" method="get">
        <div class="filter">        
            <input type="hidden" name="page" value="1"/>

            <?php
                // 검색 유형 옵션 배열
                $searchTypeArr = [
                    "senderName" => '이름',                    
                    "idx" => '회원번호',
                    "senderAddr" => '주소',
                    "senderAddrDetail" => '상세주소',
                    "senderTel" => '휴대번호'
                ];
            ?>
            <!-- 검색 유형 선택 -->
            <select id="searchType" name="searchType" class="searchType">
                <?php foreach($searchTypeArr as $data => $name){ ?>
                    <option value="<?=$data?>" <?=$data == $searchData['searchType']? 'selected' : ''?>><?=$name?></option>
                <?php } ?>
            </select>            
            
            <!-- 검색어 입력 -->
            <input type="text" id="searchTxt" name="searchTxt" class="searchTxt" value="<?=$searchData['searchTxt']?>"/>
            <i class="fas fa-search" onclick="searchForm()"></i>        
            <br/>
                        
            <div style="margin-top:10px;">
                <?php
                    // 로그인 방법 필터 옵션 배열
                    $searchTypeArr = [   
                        "all" => '전체',
                        "kakao" => '카카오',
                        "naver" => '네이버',
                        "apple" => '애플',
                        "normal" => '일반'
                    ];
                ?>
                <strong> ☑로그인방법 / </strong>
                <?php foreach($searchTypeArr as $key => $data){ ?>
                    <input type="radio" id="sns<?=$key?>" name="sns" value="<?=$key?>" <?=$searchData['sns'] == $key? 'checked' : ''?> onclick="searchForm()">
                    <label for="sns<?=$key?>"><?=$data?></label>
                <?php } ?>
                  
                <?php
                    // 회원 상태 필터 옵션 배열
                    $searchTypeArr = [   
                        "all" => '전체',
                        "remove" => '탈퇴'                        
                    ];
                ?>                
                <strong> ☑회원상태 / </strong>
                <?php foreach($searchTypeArr as $key => $data){ ?>
                    <input type="radio" id="mbState<?=$key?>" name="mbState" value="<?=$key?>" <?=$searchData['mbState'] == $key? 'checked' : ''?> onclick="searchForm()">
                    <label for="mbState<?=$key?>"><?=$data?></label>
                <?php } ?>                
            </div>
        </div>
    </form>
    
    <!-- 회원 리스트 테이블 -->
    <table style="margin-top:10px;">
        <thead>
            <tr>
                <th width="150">회원번호</th>
                <th width="150">로그인방법</th>
                <th width="200">이름</th>
                <th>주소</th>
                <th>상세주소</th>                
                <th>전화번호</th>
                <th>보유포인트</th>
                <th>포인트 내역</th>
                <th>주문 내역</th>
                <th>마지막 활동시간</th>
                <th>가입일자</th>
                <th>회원복구</th>
            </tr>
        </thead>
        <tbody>
            <!-- 회원이 없는 경우 -->
            <?php if(!count($list)){ ?>
                <tr>
                    <td colspan="12" class="empty">회원 내역이 존재하지않습니다.</td>
                </tr>
            <?php } ?>
            
            <!-- 회원 리스트 출력 -->
            <?php foreach($list as $key => $data){ ?>
                <tr style="<?=empty($data['senderName'])? 'background: #fff3f3 !important;' : ''?>">
                    <td><?=$data['idx']?></td>
                    <td>
                        <?php 
                            // 로그인 방법에 따른 값 출력
                            switch($data['sns']) {
                                case 'kakao': echo '카카오'; break;
                                case 'naver': echo '네이버'; break;
                                case 'apple': echo '애플'; break;
                                case 'normal': echo '일반'; break;
                            }                     
                        ?>                        
                    </td>
                    <td><?=empty($data['senderName'])? '미등록' : $data['senderName']?></td>
                    <td><?=empty($data['senderAddr'])? '미등록' : $data['senderAddr']?></td>
                    <td><?=empty($data['senderAddrDetail'])? '미등록' : $data['senderAddrDetail']?></td>
                    <td><?=empty($data['senderTel'])? '미등록' : $data['senderTel']?></td>                    
                    <td style="cursor: pointer; text-decoration: underline;" onclick="openPointModal(<?=$key?>)">
                        <a><?=number_format($data['point'])?>원</a>
                    </td>
                    <td>
                        <!-- 포인트 내역 링크 -->
                        <a href="/admin/point?searchType=P.memberIdx&searchTxt=<?=$data['idx']?>&state=all" style="cursor: pointer; text-decoration: underline;">포인트 내역</a>
                    </td>
                    <td style="cursor: pointer; text-decoration: underline;">
                        <!-- 주문 내역 링크 -->
                        <a href="/admin/post?page=1&searchType=W.memberIdx&searchTxt=<?=$data['idx']?>&state=A" style="cursor: pointer; text-decoration: underline;">주문 내역</a>
                    </td>
                    <td><?=$data['lastActivityDate']?></td>
                    <td><?=$data['regDate']?></td>
                    <td>
                        <?php if($data['isUse'] == 'N') { ?>
                            <!-- 회원복구 버튼 -->
                            <button onclick="restorationMember(<?=$data['idx']?>)" style="padding: 2px 5px;">회원복구</button>
                        <?php } ?>       
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    
    <!-- 페이지 네비게이션 -->
    <?php $this->load->view('/common/page', $pageData); ?>
</div>

<div id="pointModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="width: 500px;">
        <div class="modal-content">
            <div class="modal-body" style="overflow: hidden;">
                <i class="fas fa fa-times" onclick="closePointModal()"></i>                    
                                
                <strong>포인트관리</strong>
                
                <input id="lastIndex" type="hidden" value=""/>
                <p class="guide">보유포인트</p>            
                <input id="point" type="text" class="fieldInput" value="" readonly/>
                
                <p class="guide">충전포인트</p>
                <div>
                    <?
                        $pointSet = [
                            'add' => '추가',
                            'deducted' => '차감',
                        ];
                    ?>
                    <? foreach($pointSet as $key => $name){ ?>
                        <input type="radio" id="point<?=$key?>" class="setPoint" name="point" value="<?=$key?>">
                        <label for="point<?=$key?>"><?=$name?></label>
                    <? } ?>          
                </div>
                <input id="setPoint" type="text" class="fieldInput" value="" placeholder="포인트" oninput="this.value = this.value.replace(/[^0-9]/g, '');"/>
                
                <p class="guide">포인트 지급사유</p>
                <input id="ment" type="text" class="fieldInput" value="" placeholder="포인트 지급사유"/>
                
                <button class="btnSubmit" onclick="saveSetPoint()">저장하기</button>
            </div>
        </div>
    </div>
</div>

<script>
    // PHP에서 전달된 회원 목록을 JavaScript 배열로 변환
    const list = <?=json_encode($list)?>;    

    // 포인트 모달을 여는 함수
    function openPointModal(index) {        
        let data = list[index];
        
        // 모달에 데이터 입력
        $('#lastIndex').val(index);  // 모달에 있는 lastIndex 필드에 해당 회원의 인덱스를 설정
        $('#point').val(comma(data.point));  // 포인트 값을 천 단위 구분 기호로 표시
        $('#setPoint').val(0);  // 기본 포인트 값을 0으로 설정
        $('input[name="point"]').eq(0).prop('checked', true);  // 기본 포인트 유형을 선택
        $('#ment').val('');  // 포인트 지급 사유 초기화
        
        // 포인트 모달을 표시
        $('#pointModal').modal('show');        
    }

    // 포인트 모달을 닫는 함수
    function closePointModal() {
        $('#pointModal').modal('hide');
    }

    // 회원을 복구하는 비동기 함수
    async function restorationMember(memberIdx) {
        // 복구 여부 확인
        if (!confirm('해당 회원을 복구하시겠습니까?')) return;
        
        // 복구 API 호출
        const restorationMemberRes = await postJson('/adminApi/restorationMember', {            
            memberIdx: memberIdx
        });
         
        // 복구 실패 시 경고 메시지 표시
        if (!restorationMemberRes.result) {
            showAlert(restorationMemberRes.msg);
            return;
        }
        
        // 복구 성공 시 알림 후 페이지 새로고침
        showAlert('복구되었습니다.')
        .then(() => {
            location.reload();
        });
    }

    // 포인트 설정을 저장하는 비동기 함수
    async function saveSetPoint() {
        let data = list[$('#lastIndex').val()],  // 모달에서 선택된 회원의 데이터
            point = parseInt($('#setPoint').val()),  // 입력된 포인트 값
            ment = $('#ment').val();  // 입력된 지급 사유

        // 포인트와 지급 사유가 비어있으면 경고 메시지 표시
        if (!point) {
            showAlert('충전할 포인트를 입력해주세요');
            return;
        } else if (!ment) {
            showAlert('포인트 지급사유를 입력해주세요');
            return;
        }
        
        // 포인트 설정 API 호출
        const saveSetPointRes = await postJson('/pointApi/setPoint', {            
            memberIdx: data.idx,
            point: point,
            type: $('.setPoint:checked').val(),  // 선택된 포인트 유형
            ment: ment
        });
         
        // 포인트 설정 실패 시 경고 메시지 표시
        if (!saveSetPointRes.result) {
            showAlert(saveSetPointRes.msg);
            return;
        }
        
        // 설정 성공 시 알림 후 페이지 새로고침
        showAlert('저장되었습니다.')
        .then(() => {
            location.reload();
        });
    }

    // 검색 폼 제출 함수
    function searchForm() {
        $('#searchForm').submit();
    }
</script>