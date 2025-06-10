// 화면에 로딩 바를 표시하는 함수
function showLoadingBar() {    
    // 마스크 요소 생성: 화면을 덮을 투명한 배경
    let mask = `<div id='mask' style='z-index:99999998'></div>`,
        // 현재 페이지의 스크롤 위치 저장
        lodingTop = window.pageYOffset,
        // 로딩 이미지 요소 생성: 로딩 GIF를 화면 중앙에 위치시킴
        loadingImg = `<div id='loadingImg' style='position: absolute; top: calc(50% + ${lodingTop}px); left: 50%; transform: translate(-50%, -50%); z-index:99999999'>
                            <img src='/assets/image/loading.gif?animation=spin' style='width:80px; border-radius: 30%;'>
                      </div>`;

    // body에 마스크와 로딩 이미지를 추가
    $('body').append(mask).append(loadingImg);

    // 마스크의 CSS 스타일 설정: 전체 화면을 덮고, 배경을 회색으로 설정
    $('#mask').css({
        'width': '100%', 
        'height': '100vh', 
        'opacity': '0.3', 
        'position': 'absolute', 
        'top': $(window).scrollTop(), 
        'left': 0, 
        'background': '#898989'
    });
}

// 화면에 표시된 로딩 바를 숨기는 함수
function hideLoadingBar() {
    // 로딩 마스크와 로딩 이미지를 DOM에서 제거
    $('#mask, #loadingImg').remove();
}

// GET 요청을 보내고 JSON 데이터를 반환하는 함수
function fetchJson(url, isLoading = true) {
  return new Promise((resolve, reject) => {
      
      // 로딩 표시가 필요하면 로딩 바 표시
      if (isLoading) showLoadingBar();

      // 비동기 요청을 100ms 후에 실행
      setTimeout(async function() {
          try {
              const response = await fetch(url);
              const data = await response.json();

              // 요청이 완료되면 로딩 바 숨김
              if (isLoading) hideLoadingBar();

              resolve(data);
          } catch (error) {
              if (isLoading) hideLoadingBar();
              alert("fetchJson error: " + error);
              reject(error);
          }
      }, 100);
  });
}


// JSON 데이터를 서버로 POST 요청하는 함수
function postJson(url, data, isLoading = true) {
    return new Promise((resolve, reject) => {
        
        // 로딩 표시가 필요하면 로딩 바 표시
        if(isLoading) showLoadingBar();

        // 비동기 요청을 100ms 후에 실행
        setTimeout(function(){
            $.ajax({
                type: 'post', // POST 요청
                url: url, // 요청할 URL
                dataType: 'json', // 서버로부터 받을 데이터 형식은 JSON
                contentType: "application/x-www-form-urlencoded;charset=utf-8", // 요청 본문의 인코딩 형식
                async: false, // 동기 요청 (응답을 받을 때까지 기다림)
                data: data, // 서버로 보낼 데이터
                beforeSend: function(xhr) {}, // 요청 전에 실행할 함수 (빈 함수)
                success: function(data) { // 요청 성공 시 실행
                    resolve(data); // 성공한 데이터를 반환
                },
                complete: function(){ 
                    // 요청 완료 후 실행
                    if(isLoading) hideLoadingBar(); // 로딩 바 숨김
                },
                error:function(request,status,error){ // 요청 실패 시 실행
                    alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error); // 오류 메시지 표시
                }
            });
        }, 100); // 100ms 후에 AJAX 요청 실행
    });
}

// Form 데이터를 서버로 POST 요청하는 함수 (JSON 응답 받기)
function postFormJson(url, data) {    
    return new Promise((resolve, reject) => {
        
        // 로딩 바 표시
        showLoadingBar();

        // 100ms 후에 AJAX 요청 실행
        setTimeout(function(){
            $.ajax({
                type: 'post', // POST 요청
                url: url, // 요청할 URL
                dataType: 'json', // 서버로부터 받을 데이터 형식은 JSON
                timeout: 60000, // 요청 타임아웃 시간 (60초)
                contentType: 'multipart/form-data', // 멀티파트 폼 데이터로 전송
                mimeType: 'multipart/form-data', // MIME 타입 설정
                async: true, // 비동기 요청
                data: data, // 서버로 보낼 데이터 (파일 포함 가능)
                beforeSend: function(xhr) {}, // 요청 전에 실행할 함수 (빈 함수)
                success: function(data) { // 요청 성공 시 실행
                    resolve(data); // 성공한 데이터를 반환
                },
                complete: function(){ 
                    // 요청 완료 후 실행
                    hideLoadingBar(); // 로딩 바 숨김
                },
                error:function(request,status,error){ // 요청 실패 시 실행
                    alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error); // 오류 메시지 표시
                },        
                cache: false, // 캐시 사용 안 함
                contentType: false, // 콘텐츠 타입을 자동으로 설정하지 않음 (파일 업로드를 위한 설정)
                processData: false // 데이터를 자동으로 변환하지 않음 (파일 업로드를 위한 설정)
            });
        }, 100); // 100ms 후에 AJAX 요청 실행
    });
}

// POST 요청을 보내기 위해 동적으로 폼을 생성하고 제출하는 함수
function postLocation(url, param, target = '', method = 'post') {
    // 새로운 form 요소 생성
    let f = document.createElement('form');
    let objs, value;
    
    // param 객체의 key, value를 순차적으로 input 요소로 생성하여 form에 추가
    for (let key in param) {
        value = param[key];
        objs = document.createElement('input');
        objs.setAttribute('type', 'hidden'); // 사용자에게 보이지 않도록 hidden input 요소 생성
        objs.setAttribute('name', key); // input의 name 속성 설정 (전송할 데이터의 이름)
        objs.setAttribute('value', value); // input의 value 속성 설정 (전송할 데이터의 값)
        f.appendChild(objs); // form에 input 요소 추가
    }    

    // 만약 target 값이 설정되어 있으면 form의 target 속성 설정
    if (target) {
        f.setAttribute('target', target); // 새 창 또는 특정 프레임에서 결과를 표시할 때 사용
    }
    
    // 폼의 method와 action 속성 설정 (method는 기본값 'post', action은 url로 설정)
    f.setAttribute('method', method);
    f.setAttribute('action', url);
    
    // 문서의 body에 form을 추가하고, form을 제출
    document.body.appendChild(f);
    f.submit(); // 폼을 제출하여 서버로 데이터 전송
}

// swal 기본 스타일로 경고 메시지를 표시하는 함수
function showAlert(message, destroyEvent) {
    return swal.fire({
        html: message, // 경고 메시지 내용
        confirmButtonText: '확인', // 확인 버튼 텍스트
        didDestroy: () => {
            if (destroyEvent) {
                setTimeout(function(){
                    destroyEvent; // destroyEvent가 존재하면 실행
                }, 0);
            }
        }
    });
}

// confirm 스타일로 메시지를 표시하고 확인/취소 버튼을 반환하는 함수
const showConfirm = (message) => {
    return Swal.fire({
        html: message, // 확인 메시지 내용
        confirmButtonText: '확인', // 확인 버튼 텍스트
        denyButtonText: '취소', // 취소 버튼 텍스트
        showDenyButton: true // 취소 버튼을 표시
    });
}

// 주어진 날짜 문자열을 'YYYY-MM-DD' 형식으로 포맷하는 함수
function formatDateFromDateString(dateString) {
    // Date 객체 생성 (주어진 날짜 문자열을 파싱)
    var date = new Date(dateString);

    // 원하는 형식으로 날짜 포맷팅
    var year = date.getFullYear(); // 연도 추출
    var month = String(date.getMonth() + 1).padStart(2, '0'); // 월 추출 (0부터 시작하므로 +1)
    var day = String(date.getDate()).padStart(2, '0'); // 일 추출

    // 결과 반환 (YYYY-MM-DD 형식)
    return year + "-" + month + "-" + day;
}

// 다움 우편번호 서비스를 열고, 선택된 주소에 대해 사서함 번호 범위를 추출하여 표시하는 함수
function openDaumPostcode($zip_code, $addr, $addr_detail, index) {
    const kakaoLayer = document.getElementById('layer'); // 우편번호 레이어 요소를 찾기
    
    new daum.Postcode({
        oncomplete: function(data) {
            let addr = '';
            
            console.log(data);
            // 사용자가 선택한 주소 종류에 따라 도로명 주소 또는 지번 주소를 선택
            if (data.userSelectedType === 'R') { // 도로명 주소 선택
                addr = data.roadAddress;
            } else { // 지번 주소 선택
                addr = data.jibunAddress;
            }
            
            // 주소에서 사서함 범위 추출
            const result = extractPostboxRange(addr);
            console.log(result);
            
            // 우편번호, 주소 및 상세주소 필드를 채움
            $zip_code.val(data.zonecode); // 우편번호 입력
            $addr.val(`(${data.zonecode})${addr}`); // 주소 입력
            $addr_detail.val(data.buildingName).attr('disabled', false); // 건물명 입력
            $addr_detail.prev(`.guide${index}`).remove(); // 기존 가이드 메시지 삭제
            
            // 사서함 범위가 있는 경우 처리
            if (!(result.min === 0 && result.max === 0)) {                
                if (result.min == result.max) { // 범위가 동일한 경우
                    $addr_detail.val(result.min);
                } else { // 범위가 다른 경우
                    $addr_detail.before(`<p class="letterMent guide${index}" style="margin-top: 0px;">✅범위 (${result.min} ~ ${result.max})에 대한 사서함번호 지정</p>`);
                    $addr_detail.attr('placeholder', `범위 (${result.min} ~ ${result.max})에 대한 사서함번호 지정`); // placeholder 설정
                }
            } else {
                $addr_detail.attr('placeholder', `상세주소`); // 상세주소 입력
            }
            
            closeDaumPostcode(); // 우편번호 서비스 레이어 닫기
        },
        width: '100%', // 레이어의 너비
        height: '100%', // 레이어의 높이
        maxSuggestItems: 10 // 최대 추천 아이템 개수
    }).embed(kakaoLayer);
    
    kakaoLayer.style.display = 'block'; // 레이어를 표시
}

// 다음 우편번호 서비스 레이어를 닫는 함수
function closeDaumPostcode() {        
    const kakaoLayer = document.getElementById('layer'); // 레이어 요소 찾기
    
    kakaoLayer.style.display = 'none'; // 레이어를 숨기기
}

// 주소에서 사서함 범위를 추출하는 함수
function extractPostboxRange(address) {
    // 사서함 범위를 찾기 위한 정규식
    const regex = /사서함\s(\d+)(?:-(\d+))?(?:\s~\s(\d+)(?:-(\d+))?)?/;
    const match = address.match(regex);
    let min = 0, max = 0;
    
    if (match) {        
        if (match[3]) {
            // 범위가 있는 경우 (예: 사서함 123-456 ~ 789-101112)
            min = match[1] + (match[2] ? `-${match[2]}` : "");
            max = match[3] + (match[4] ? `-${match[4]}` : "");
        } else {
            // 단일 값인 경우 (예: 사서함 123-456)
            min = max = match[1] + (match[2] ? `-${match[2]}` : "");
        }        
    }
    
    return { min, max }; // 최소값과 최대값을 객체로 반환
}

// 쿠키를 설정하는 함수 (기본적으로 30일 후 만료)
function setCookie(name, value, days = 30) {
    let expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000)); // 날짜 계산
        expires = "; expires=" + date.toUTCString(); // UTC 형식으로 만료 날짜 설정
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/"; // 쿠키 설정
}

// 쿠키를 삭제하는 함수
function deleteCookie(name) {
    document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;"; // 만료된 날짜로 설정하여 삭제
}

// 쿠키 값을 가져오는 함수
function getCookie(name) {
    var nameEQ = name + "="; // 찾을 쿠키의 이름
    var cookies = document.cookie.split(';'); // 모든 쿠키를 세미콜론으로 분리
    for(var i = 0; i < cookies.length; i++) {
        var cookie = cookies[i].trim(); // 공백 제거
        if (cookie.indexOf(nameEQ) === 0) { // 쿠키가 존재하면
            return cookie.substring(nameEQ.length, cookie.length); // 값 반환
        }
    }
    return null; // 찾을 수 없으면 null 반환
}

// 숫자에 천 단위 구분기호 콤마를 추가하는 함수
function comma(str){
    str = String(str); // 문자열로 변환
    return str.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,'); // 정규식을 이용한 천 단위 구분
}

// 숫자에서 천 단위 구분기호를 제거하는 함수
function uncomma(str){
    str = String(str); // 문자열로 변환
    return str.replace(/[^\d]+/g, ''); // 숫자가 아닌 문자를 제거
}

// 문자열에서 줄 바꿈을 <br /> 태그로 변환하는 함수
function nl2br(str){
    str = String(str); // 문자열로 변환
    return str.replace(/\n/g, "<br />"); // 줄 바꿈을 <br />로 변환
}

// 이메일 유효성 검사 함수
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // 이메일 형식 정규식
    return regex.test(email); // 이메일이 형식에 맞는지 검사
}

// 전화번호 유효성 검사 함수 (11자리)
function validatePhone(phoneNumber){
    return phoneNumber.length == 11; // 전화번호 길이가 11자리인지 검사
}

// 숫자만 입력되도록 변환하는 함수
function validateNumberInput(input) {
    input.value = input.value.replace(/[^0-9]/g, ''); // 숫자가 아닌 문자는 모두 제거
}

// 전화번호 형식화 함수 (하이픈 추가)
function formatPhoneNumber(phoneNumber) {
    // 숫자 이외의 문자는 제거
    let cleaned = ('' + phoneNumber).replace(/\D/g, '');

    // 전화번호 길이에 맞는 형식으로 분리
    let match = cleaned.match(/^(\d{3})(\d{3,4})(\d{4})$/);

    if (match) {
        // 하이픈을 추가한 형식으로 반환
        return `${match[1]}-${match[2]}-${match[3]}`;
    }

    // 형식에 맞지 않으면 원본을 그대로 반환
    return phoneNumber;
}

// 주소에서 우편번호와 주소를 추출하는 함수
function extractAddressInfo(address) {
    // 우편번호와 주소를 추출하는 정규식
    const regex = /^\((\d{5})\)\s*(.+)$/;
    const match = address.match(regex);

    if (match) {
        // 우편번호와 주소를 객체로 반환        
        return {
            postCode: match[1],
            address: match[2]
        };
    }    

    // 매칭되지 않으면 null 반환
    return null;
}

/* 유니크한 랜덤번호 생성 함수 */
function generateUniqueRandomString(length = 36) {
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'; // 사용할 문자 목록
    let result = ''; // 랜덤 문자열을 저장할 변수
    const usedCharacters = new Set(); // 이미 사용된 문자를 추적할 Set 객체

    while (result.length < length) {
        const randomIndex = Math.floor(Math.random() * characters.length); // 랜덤 인덱스 생성
        const randomChar = characters[randomIndex]; // 해당 인덱스의 문자 선택

        if (!usedCharacters.has(randomChar)) { // 해당 문자가 이미 사용되지 않았으면
            result += randomChar; // 결과 문자열에 추가
            usedCharacters.add(randomChar); // 사용된 문자로 추가
        }

        // 만약 필요한 길이보다 유니크한 문자가 적다면 반복문을 무한정 돌지 않도록 추가적인 조치를 할 수 있습니다.
        if (usedCharacters.size === characters.length) {
            break; // 더 이상 유니크한 문자가 없으면 종료
        }
    }

    return result; // 최종 랜덤 문자열 반환
}

/* 스크롤 차단 함수 */
function disableScroll() {
    document.addEventListener('touchmove', preventScroll, { passive: false }); // touchmove 이벤트에 preventScroll 적용
}

/* 스크롤 허용 함수 */
function enableScroll() {
    document.removeEventListener('touchmove', preventScroll); // touchmove 이벤트에서 preventScroll 제거
}

/* 스크롤 방지 이벤트 핸들러 */
function preventScroll(event) {
    event.preventDefault(); // 기본 동작을 막아서 스크롤을 방지
}