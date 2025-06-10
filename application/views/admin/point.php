<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.14.3/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.8/FileSaver.min.js"></script>

<p class="title">포인트 관리</p>

<div id="notice">    
    <div class="local">
        <!-- 총 포인트 로그 개수를 표시 -->
        총 포인트로그 <?=$pageData['totalCnt']?>개
    </div>

    <!-- 검색 폼 -->
    <form id="searchForm" method="get">
        <div class="filter">        
            <input type="hidden" name="page" value="1"/>

            <?php
                // 검색 타입을 설정
                $searchTypeArr = [
                    "M.senderName" => '회원이름',
                    "P.memberIdx" => '회원번호'
                ];
            ?>
            <select id="searchType" name="searchType" class="searchType">
                <!-- 검색 타입에 따른 선택 옵션을 동적으로 생성 -->
                <?php foreach($searchTypeArr as $data => $name){ ?>
                    <option value="<?=$data?>" <?=$data == $searchData['searchType']? 'selected' : ''?>><?=$name?></option>
                <?php } ?>
            </select>            
            <input type="text" id="searchTxt" name="searchTxt" class="searchTxt" value="<?=$searchData['searchTxt']?>"/>
            <i class="fas fa-search" onclick="searchForm()"></i>        
            <br/>
            
            <?php
                // 상태 필터 설정
                $searchTypeArr = [ 
                    "wait" => '입금대기',
                    "all" => '전체'                    
                ];
            ?>
            
            <div style="margin-top:10px;">
                <strong> ☑상태 / </strong>
                <!-- 상태에 따른 라디오 버튼 생성 -->
                <?php foreach($searchTypeArr as $key => $data){ ?>
                    <input type="radio" id="state<?=$key?>" name="state" value="<?=$key?>" <?=$searchData['state'] == $key? 'checked' : ''?> onclick="searchForm()">
                    <label for="state<?=$key?>"><?=$data?></label>
                <?php } ?>
            </div>
        </div>
    </form>
    
    <!-- 포인트 로그 테이블 -->
    <table style="margin-top:10px;">
        <thead>
            <tr>
                <th width="250">회원번호</th>
                <th width="150">회원이름</th>
                <th width="150">상태</th>
                <th width="150">입금내역 삭제</th>
                <th width="200">포인트</th>
                <th width="200">보너스</th>
                <th>포인트명</th>
                <th width="200">일자</th>                
            </tr>
        </thead>
        <tbody>
            <!-- 포인트 내역이 없을 경우 -->
            <?php if(!count($list)){ ?>
                <tr>
                    <td colspan="8" class="empty">포인트 내역이 존재하지않습니다.</td>
                </tr>
            <?php } ?>
            
            <!-- 포인트 로그 리스트 출력 -->
            <?php foreach($list as $key => $data){ ?>
                <tr>
                    <td>                        
                        <!-- 회원번호를 클릭하면 회원 상세 페이지로 이동 -->
                        <a href="/admin/member?searchType=idx&searchTxt=<?=$data['memberIdx']?>" style="cursor: pointer; text-decoration: underline;" href="">
                            <?=$data['memberIdx']?>(회원상세)
                        </a>
                    </td>
                    <td>
                        <?=empty($data['senderName'])? '미등록' : $data['senderName']?>
                    </td>
                    <td>
                        <!-- 상태가 '입금대기'일 경우 입금 확인 버튼 추가 -->
                        <?php if($data['isWait'] == 'Y') { ?>
                            <p>입금대기(<?=$data['depositorName']?>)</p>
                            <button class="btnDownload" style="margin: 0; margin-top: 5px; border: 0; background: #000000; color: #fff;" onclick="checkDeposit(<?=$data['idx']?>)">
                                입금확인
                            </button>                                                    
                        <?php } else { ?>
                            <p>완료</p>
                        <?php } ?>
                        
                        <? if($data['isCashReceipt'] == 'Y') { ?>
                            <button onclick="downCashReceipt(<?=$key?>)" style="background: #03a603; color: #fff; padding: 5px;">현금영수증</button>
                        <? } ?>
                    </td>
                    <td>
                        <!-- 상태가 '입금대기'일 경우 입금내역 삭제 버튼 추가 -->
                        <?php if($data['isWait'] == 'Y') { ?>
                        <button class="btnDownload" style="margin: 0; margin-top: 5px; border: 0; background: #ff4040; color: #fff;" onclick="deletePointLog(<?=$data['idx']?>)">
                            입금내역 삭제
                        </button>
                        <?php } ?>                        
                    </td>
                    <td><?=number_format($data['point'] - $data['bonus'])?>원</td>
                    <td><?=number_format($data['bonus'])?>원</td>
                    <td><?=$data['ment']?></td>
                    <td><?=$data['regDate']?></td>                        
                </tr>
            <?php } ?>
        </tbody>
    </table>
    
    <!-- 페이지네이션 -->
    <?php $this->load->view('/common/page', $pageData); ?>
</div>

<!-- 포인트 관리 모달 -->
<div id="pointModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="width: 500px;">
        <div class="modal-content">
            <div class="modal-body" style="overflow: hidden;">
                <i class="fas fa fa-times" onclick="closePointModal()"></i>                    
                                  
                <strong>포인트관리</strong>
                
                <!-- 숨겨진 인덱스 입력 필드 -->
                <input id="lastIndex" type="hidden" value=""/>
                
                <p class="guide">보유포인트</p>            
                <input id="point" type="text" class="fieldInput" value="" readonly/>
                
                <p class="guide">충전포인트</p>
                <div>
                    <?php
                        // 포인트 추가/차감 옵션
                        $pointSet = [
                            'add' => '추가',
                            'deducted' => '차감',
                        ];
                    ?>
                    <?php foreach($pointSet as $key => $name){ ?>
                        <input type="radio" id="point<?=$key?>" class="setPoint" name="point" value="<?=$key?>">
                        <label for="point<?=$key?>"><?=$name?></label>
                    <?php } ?>          
                </div>
                
                <!-- 포인트 입력 필드 -->
                <input id="setPoint" type="text" class="fieldInput" value="" placeholder="포인트" oninput="this.value = this.value.replace(/[^0-9]/g, '');"/>
                
                <p class="guide">포인트 지급사유</p>
                <input id="ment" type="text" class="fieldInput" value="" placeholder="포인트 지급사유"/>
                
                <!-- 저장 버튼 -->
                <button class="btnSubmit" onclick="saveSetPoint()">저장하기</button>
            </div>
        </div>
    </div>
</div>


<script>
    var pointList = <?=json_encode($list)?>;  // 포인트 목록
    
    // 입금 확인 함수
    async function checkDeposit(pointLogIdx) {
        // 사용자에게 확인을 받음
        if(!confirm('입금확인 처리하시겠습니까?')) return;
        
        // 서버에 입금 확인 요청
        const checkDepositRes = await postJson('/pointApi/checkDeposit', {            
            pointLogIdx: pointLogIdx
        });
         
        // 서버 응답이 실패한 경우, 메시지 출력
        if(!checkDepositRes.result){
            showAlert(checkDepositRes.msg);
            return;
        }
        
        // 성공적으로 입금 처리된 경우 알림 후 페이지 새로고침
        showAlert('입금처리 되었습니다.')
        .then(() => {
            location.reload(); // 페이지를 새로고침하여 변경사항 반영
        });    
    }
    
    // 입금 내역 삭제 함수
    async function deletePointLog(pointLogIdx) {
        // 사용자에게 확인을 받음
        if(!confirm('해당 입금내역을 삭제처리 하시겠습니까?')) return;
        
        // 서버에 입금 내역 삭제 요청
        const deletePointLogRes = await postJson('/pointApi/deletePointLog', {            
            pointLogIdx: pointLogIdx
        });
         
        // 서버 응답이 실패한 경우, 메시지 출력
        if(!deletePointLogRes.result){
            showAlert(deletePointLogRes.msg);
            return;
        }
        
        // 성공적으로 삭제된 경우 알림 후 페이지 새로고침
        showAlert('삭제처리 되었습니다.')
        .then(() => {
            location.reload(); // 페이지를 새로고침하여 삭제된 내역 반영
        });    
    }
    
    // 검색 폼 제출 함수
    function searchForm() {
        $('#searchForm').submit(); // 검색 폼을 서버로 전송
    }
    
     /* 현금영수증 정보 다운로드 */
     function downCashReceipt(index) {
        let data = pointList[index];

        // 날짜 기반 파일명 생성 (YYYYMMDD.xlsx)
        let today = new Date();
        let fileDate = today.getFullYear().toString() + 
                    String(today.getMonth() + 1).padStart(2, '0') + 
                    String(today.getDate()).padStart(2, '0');
        
        let fileName = `${fileDate}.xlsx`;

        let jsonData = [
            ["엑셀 업로드 양식(현금영수증)", "", "", "", "", "", "", ""],
            [
                "○ 현재 시트에 발급할 내용을 입력하시기 바랍니다.('메모'는 선택 입력 사항이며 나머지 5개 항목은 모두 빠짐없이 작성)",
                "> 실제 업로드할 내용은 7행부터 입력하여야 하고, 최대 1,000건까지 입력 가능합니다.",
                "> 6개 항목 작성 시 [항목설명]시트를 참고하시고, 총거래금액/공급가액/부가가치세는 [부가가치세 계산식]시트를 이용해 복사하시면 편리합니다.",
                "> 총거래금액/공급가액/부가가치세 항목에 엑셀 함수(수식)가 포함되면 오류가 발생하므로 0 이상의 숫자와 ,(쉼표)만 입력하여야 합니다.",
                "> 발급할 내용 입력시 [올바른 예시], [잘못된 예시]를 참고하시기 바랍니다.",
                "> 일괄 발급 시 오류가 나오는 경우 [검증결과 오류코드 설명]시트를 참고하시어 오류 항목을 수정하시기 바랍니다.",
                "> 임의로 행을 추가/삭제 하시면 오류가 발생할 수 있습니다.",
                ""
            ],
            ["○ 고객요청 없이 자진발급하는 경우에는 발급수단번호에 010-000-1234로 입력하고, 용도구분은 0(소득공제용)으로 입력하여야 합니다."],
            [
                "○ 현재 시트에 내용 입력이 완료되면, [항목설명] 등 다른 시트를 삭제(수정)하지 말고 그대로 업로드하여 [파일검증], [일괄발급] 바랍니다.",
                "> 입력하는 각 항목의 셀 서식은 텍스트, 숫자 모두 가능하며, 엑셀 파일 확장자는 XLS, XLSX 모두 가능합니다."
            ],
            ["○ 현금영수증 일괄 발급에 어려움이 있으신 경우 국세상담센터(국번없이 126 → 1번 → 1번)로 문의주시기 바랍니다."],
            ["용도구분", "발급수단번호", "총거래금액(합계)", "공급가액", "부가가치세", "메모", "", ""],
            [
                data.cashReceiptType === 'earnings' ? '0' : '1', // 용도구분
                data.cashReceiptNumber.toString(), // 발급수단번호
                data.point - data.bonus, // 총거래금액
                data.point - data.bonus, // 공급가액
                0, // 부가가치세
                `"포인트충전" - ${data.cashReceiptEmail}` // 메모
            ]
        ];

        // 워크북 생성
        var wb = XLSX.utils.book_new();

        // 워크시트 생성
        var newWorksheet = XLSX.utils.aoa_to_sheet(jsonData);

        // 행 높이 설정
        newWorksheet['!rows'] = [
            { hpx: 30 },  // 첫 번째 행 높이
            { hpx: 137 }, // 설명 행
            { hpx: 20 },  // 고객 요청 관련
            { hpx: 20 },
            { hpx: 20 },
            { hpx: 20 },
            { hpx: 20 }
        ];

        // 열 너비 설정
        newWorksheet['!cols'] = [
            { wch: 20 }, // 상품명
            { wch: 15 }, // 거래금액
            { wch: 15 }, // 공급가액
            { wch: 10 }, // VAT
            { wch: 10 }, // 면세금액
            { wch: 15 }, // 구매자명
            { wch: 30 }, // 구매자 이메일
            { wch: 20 }, // 발급할 번호
            { wch: 10 }  // 용도
        ];

        // 발급할 번호를 텍스트 형식으로 유지
        for (let cell in newWorksheet) {
            if (cell.startsWith('H')) { // "발급할 번호" 컬럼
                newWorksheet[cell].z = '@'; // 텍스트 형식 지정
            }
        }

        // 워크북에 워크시트 추가
        XLSX.utils.book_append_sheet(wb, newWorksheet, "현금영수증");

        // 워크북을 엑셀 파일로 변환
        var wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'binary' });

        // Blob을 생성하여 파일로 저장 (YYYYMMDD.xlsx 형식)
        saveAs(new Blob([s2ab(wbout)], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" }), fileName);
    }

    // Blob을 위한 binary 변환 함수
    function s2ab(s) {
        var buf = new ArrayBuffer(s.length);
        var view = new Uint8Array(buf);
        for (var i = 0; i < s.length; i++) {
            view[i] = s.charCodeAt(i) & 0xFF;
        }
        return buf;
    }

</script>