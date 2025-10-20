<style>
	.mailbox-new-alert-top {
		position: fixed;
		top: 20px;
		left: 50%;
		transform: translateX(-50%);
		background: #fff8e1;
		color: #333;
		border: 1px solid #ffd54f;
		padding: 14px 20px;
		border-radius: 16px;
		box-shadow: 0 4px 12px rgba(0,0,0,0.1);
		font-size: 15px;
		display: flex;
		align-items: center;
		gap: 10px;
		z-index: 9999;
		animation: slideDown 0.5s ease-out, fadeOut 5s ease-in forwards;
		cursor: pointer;
		min-width: 60%;
	}

	.mailbox-new-alert-top .mail-icon {
		width: 28px;
		height: 28px;
	}

	@keyframes slideDown {
		from { opacity: 0; transform: translateX(-50%) translateY(-20px); }
		to { opacity: 1; transform: translateX(-50%) translateY(0); }
	}

	@keyframes fadeOut {
		0%, 85% { opacity: 1; }
		100% { opacity: 0; }
	}
</style>



<div id="home">
	<div class="homeBox">
		<? for($i=1; $i<=2; $i++){ ?>
			<div class="box box<?=$i?> <?=$i>=2? 'hide' : ''?>">

				<? if(!empty($this->user)){ ?>
					<p class="introMent">
						<?=!empty($this->user['nickname'])? $this->user['nickname'] : '회원'?>님
						안녕하세요!🙇
					</p>
				<? } ?>

				<? if($i==1){ ?>
					<p class="ment">이제 소중한 사람들에게</p>
					<p class="ment"><span class="point">편지</span>를 적어볼까요?</p>
					<img class="mainImage message" src="/assets/image/message.png">
				<? }else{ ?>
					<p class="ment">진심을 담아</p>
					<p class="ment"><span class="point">편지</span>를 전송해보세요!</p>
					<img class="mainImage human" src="/assets/image/human.png">
				<? } ?>
			</div>
		<? } ?>
		<div style="display: flex; align-items: center; justify-content: center; flex-direction: column;">
			<? if(empty($this->user)){ ?>
				<a href="javascript:nav.locationHref('/login', 'a2')" class="letterBtn" style="background: linear-gradient(272deg, #FFD600 0.5%, #FFDF34 93.87%); margin-bottom: 10px;">로그인</a>
				<a href="javascript:nav.locationHref('/letter', 'b1')" class="purpleBack letterBtn">비회원 편지쓰기</a>
				<a href="javascript:nav.locationHref('/mypage/searchLetter', 'b2')" class="searchBtn">마이페이지 / 비회원 주문 조회</a>
			<? }else{ ?>
				<div id="sendBox" style="margin-bottom: 10px;">
					<p style="font-size: 18px; font-weight: bold; letter-spacing: -1px;">📦발송안내</p>
					<p id="countdown" style="margin-top: 5px; display: block;color: rgba(0, 0, 0, 0.6); letter-spacing: -1px; margin: 5px 0;"></p>
				</div>
				<a href="javascript:nav.locationHref('/letter', 'b1')" class="purpleBack letterBtn">편지 적으러 가기</a>
				<a href="javascript:nav.locationHref('/mypage/myLetter', 'b4')" class="searchBtn">내가 쓴 편지</a>
			<? } ?>
		</div>

		<div class="arrowLeft" onclick="moveStep()">
		</div>
		<div class="arrowRight" onclick="moveStep()">
			<img class="arrow" src="/assets/image/ico/ico_arrow_right.png">
		</div>

		<? if(!isWebView()){ ?>
			<div class="storeBox">
				<a href="https://play.google.com/store/apps/details?id=com.dongldoit" target="_blank">
					<img src="/assets/image/ico/ico_google.png">
					Google Play
				</a>

				<a href="https://apps.apple.com/kr/app/%EB%8F%99%EA%B8%80/id6642679180" target="_blank">
					<img src="/assets/image/ico/ico_apple.png">
					App Store
				</a>
			</div>
		<? } ?>

		<div class="companyInfo" onclick="showCompayInfo()" style="cursor:pointer;">
			<span><strong>동글 정보 자세히보기</strong></span>
		</div>

		<div id="companyInfo" class="companyInfo hide" style="margin-top: 5px;">
            <span>
                <a href="javascript:nav.locationHref('/app/provision', 'y1')">이용약관</a> |
                <a href="javascript:nav.locationHref('/app/privacy', 'y2')">개인정보처리방침</a>
            </span>
			<span>동글</span>
			<span>사업자등록번호: 401-17-62774</span>
			<span>통신판매업번호: 2024-부산사하-0698</span>
			<span>전화번호: 010-4674-6705</span>
			<div>
				<span style="display: inline-block;">계좌번호: 기업 28810139201013</span>
				<button onclick="copyToClipboard('기업 28810139201013')" style="margin-left: 2px; padding: 3px; display: inline-block;">복사</button>
			</div>
			<span>주소: 부산광역시 서구 충무대로91 101-2401호</span>
			<span>대표자: 황종민</span>
			<span>COPYRIGHT (C) 2024 동글. ALL RIGHTS RESERVED.</span>
		</div>
	</div>

	<? foreach($popupList as $data) { ?>

		<? if(empty(get_cookie('isHidePopup_'.$data['idx']))) { ?>
			<div class="popup popupContainer show" style="display: flex; flex-direction: column; align-items: center;">
				<img src="<?=$data['onebonImgPath']?>" style="max-width: 500px; width:100%; max-height: 90vh; padding: 0 10px;"/>
				<div style="margin-top:10px; display:flex; gap:50px;">
					<a style="color:#fff; text-decoration: underline; cursor:pointer;" onclick="closePopup($(this), <?=$data['idx']?>)">24시간 동안 보지 않기</a>
					<a style="color:#fff; text-decoration: underline; cursor:pointer;" onclick="$(this).closest('.popup').hide();">닫기</a>
				</div>
			</div>
		<? } ?>

	<? } ?>

	<? if(!empty($this->user) && $this->user['isNda'] == 'N') { ?>
		<div id="nda" class="popupContainer show">
			<div class="ndaBox" style="    max-width: 560px; padding: 0 10px; width: 95%; max-height: 90vh; overflow-y: scroll; white-space: pre-line; background: #fff; border-radius: 10px;">
				<b>비밀유지서약서 (NDA, Non-Disclosure Agreement)</b>

				제 1 조 (목적)
				본 계약은 **동글(이하 “회사”라 한다)**와 고객(이하 “고객”이라 한다) 간의 신뢰를 기반으로 하며, 고객이 회사의 서비스를 이용하는 과정에서 제공하는 개인정보 및 편지 내용을 보호하기 위한 목적으로 체결된다.

				제 2 조 (비밀 정보의 정의)
				본 계약에서 “비밀 정보”란 고객이 회사에 제공하는 다음의 정보를 포함한다.
				1.	고객이 작성한 편지의 내용(문서, 이미지, 첨부 파일 등)
				2.	고객의 성명, 주소, 연락처 등 개인정보
				3.	고객과 관련된 기타 기밀 사항

				제 3 조 (비밀 유지 의무)
				1.	회사는 고객의 비밀 정보를 고객의 사전 동의 없이 제3자에게 제공, 공개 또는 공유할 수 없다.
				2.	회사는 비밀 정보를 보호하기 위해 적절한 보안 조치를 취하며, 내부 직원 또는 협력업체가 이를 무단으로 열람하거나 유출하지 않도록 관리해야 한다.
				3.	회사는 본 계약이 종료된 후에도 비밀 정보를 보호할 책임이 있으며, 고객이 요청할 경우 즉시 정보를 폐기해야 한다.

				제 4 조 (예외 사항)
				다음의 경우, 회사는 고객의 사전 동의 없이 정보를 제공할 수 있다.
				1.	법원의 명령 또는 관계 법령에 따라 정보 제공이 요구되는 경우
				2.	수사 기관이 적법한 절차에 따라 정보 제공을 요청하는 경우
				3.	고객이 사전 동의를 통해 특정 기관이나 제3자에게 정보 제공을 허용한 경우

				제 5 조 (책임 및 손해배상)
				1.	회사가 본 계약을 위반하여 고객의 비밀 정보를 유출하거나 무단으로 활용한 경우, 고객은 이에 대한 손해배상을 청구할 수 있다.
				2.	회사는 비밀 정보 유출로 인해 고객에게 발생한 직접적 및 간접적 손해에 대해 법적 책임을 진다.
				3.	회사의 과실로 인해 정보 유출이 발생한 경우, 고객은 법적 대응을 포함한 조치를 취할 수 있으며, 회사는 이에 적극 협조해야 한다.

				제 6 조 (계약의 해지 및 효력 기간)
				1.	본 계약은 고객이 회사의 서비스를 이용하는 기간 동안 유효하며, 서비스 이용 종료 후에도 3년간 유지된다.
				2.	고객이 요청할 경우, 회사는 즉시 고객의 정보를 폐기하고, 이에 대한 확인서를 제공해야 한다.

				제 7 조 (준거법 및 관할법원)
				1.	본 계약과 관련된 분쟁이 발생할 경우, 대한민국 법률을 따르며, 관할 법원은 부산지방법원으로 한다.

				부칙
				본 계약은 고객이 회사의 이용약관에 동의하는 시점부터 효력이 발생하며, 회사는 본 계약의 내용을 성실히 준수할 의무가 있다.

				서명 및 동의

				위 내용을 충분히 숙지하였으며, 이에 동의합니다.

				회사명: 동글
				대표자: 황종민
				날짜: <?=date('Y년 m월 d일')?>


				고객 성명: <input id="ndaName" type="text" value="<?=$this->user['senderName']?>" style="width: 200px; border: 1px solid black; padding-left: 6px;"/>
				날짜: <?=date('Y-m-d')?>
				<button style="width: 100%; height: 40px; margin: 14px 0;" onclick="agreeNda()">동의</button>
			</div>
		</div>
	<? } ?>
</div>

<?php if (!empty($this->user) && !empty($hasNewMailbox)) { ?>
	<div id="mailboxNewAlert" class="mailbox-new-alert-top" onclick="location.href='/mypage/myMailbox'">
		<span class="emoji">📬</span>
		<div class="msg">
			<strong>새로운 편지 도착!</strong><br>
			사서함에서 확인해보세요 💌
		</div>
	</div>
<?php } ?>





<script>
	var mainStep = 1;

	// 팝업 닫기 함수
	function closePopup($this, idx){
		$this.closest('.popup').remove();

		if(idx) {
			setCookie(`isHidePopup_${idx}`, 'Y', 1);
		}
	}

	// NDA 동의 처리 함수
	async function agreeNda() {
		let $ndaName = $('#ndaName');

		// 이름 입력 여부 확인
		if(!$ndaName.val()) {
			showAlert('동의하시는 고객님의 성명을 입력해주세요.');
			return;
		}

		// NDA 동의 요청
		const agreeNdaRes = await postJson('/userApi/agreeNda', {
			ndaName: $ndaName.val()
		});

		// 요청 실패 시 알림 표시
		if (!agreeNdaRes.result) {
			showAlert(agreeNdaRes.msg);
			return false;
		}

		// NDA 동의 후 화면에서 제거
		$('#nda').remove();
	}

	// 회사 정보 표시 토글 함수
	function showCompayInfo() {
		$('#companyInfo').toggleClass('hide');
	}

	// 스텝 이동 함수
	function moveStep() {
		mainStep++;
		if (mainStep > 2) {
			mainStep = 1;
		}

		$('.box').addClass('hide');
		$(`.box${mainStep}`).removeClass('hide');
	}

	// 공휴일 목록 (예시: YYYY-MM-DD 형식)
	const holidays = [
		'2025-01-01', // 1일 신정 (월)
		'2025-01-27', // 설날 연휴
		'2025-01-28', // 설날 연휴
		'2025-01-29', // 설날 연휴
		'2025-01-30', // 설날 연휴
		'2025-03-03', // 3월 1일 삼일절 (금)
		'2025-05-05', // 5월 5일 어린이 날 (일)
		'2025-05-06', // 5월 6일 대체공휴일 (어린이 날) (월)
		'2025-06-03', // 6월 6일 현충일 (목)
		'2025-06-06', // 6월 6일 현충일 (목)
		'2025-08-15', // 8월 15일 광복절 (목)
		'2025-10-03', // 10월 3일 개천절 (목)
		'2025-10-06', // 10월 3일 개천절 (목)
		'2025-10-07', // 10월 3일 개천절 (목)
		'2025-10-08', // 10월 3일 개천절 (목)
		'2025-10-09', // 10월 9일 한글날 (수)
		'2025-12-25', // 12월 25일 크리스마스 (수)
	];

	function getKSTDate() {
		const now = new Date();
		// UTC 시간 + 9시간 (KST)
		const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
		return new Date(utc + (9 * 60 * 60 * 1000));
	}


	function formatDateKST(date) {
		const kst = new Date(date.getTime() + 9 * 60 * 60 * 1000); // UTC+9 보정
		const yyyy = kst.getFullYear();
		const mm = (kst.getMonth() + 1).toString().padStart(2, '0');
		const dd = kst.getDate().toString().padStart(2, '0');
		return `${yyyy}-${mm}-${dd}`;
	}

	// 특정날짜가 공휴일 또는 주말인지 확인하는 함수
	function isHolidayOrWeekend(date) {
		const day = date.getDay();
		const formattedDate = formatDateKST(date);
		return day === 0 || day === 6 || holidays.includes(formattedDate);
	}


	// 다음 유효한 날짜 구하기
	function getNextValidDate(date) {
		let nextDate = new Date(date);
		while (isHolidayOrWeekend(nextDate)) {
			nextDate.setDate(nextDate.getDate() + 1);
		}
		return nextDate;
	}

	function startCountdown() {
		function getKSTDate() {
			const now = new Date();
			const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
			return new Date(utc + (9 * 60 * 60 * 1000));
		}

		let now = getKSTDate();
		const baseDate = new Date(now.getFullYear(), now.getMonth(), now.getDate());
		let targetDate = baseDate;

		if (isHolidayOrWeekend(baseDate)) {
			targetDate = getNextValidDate(baseDate);
		}

		// 2025.06.05 edit time
		//let target = new Date(targetDate.getFullYear(), targetDate.getMonth(), targetDate.getDate(), 16, 0, 0);
		let target = new Date(targetDate.getFullYear(), targetDate.getMonth(), targetDate.getDate(), 16, 30, 0);

		if (now > target) {
			const nextDay = new Date(baseDate.getTime() + 24 * 60 * 60 * 1000);
			targetDate = getNextValidDate(nextDay);

			// 2025.06.05 edit time
			//target = new Date(targetDate.getFullYear(), targetDate.getMonth(), targetDate.getDate(), 16, 0, 0);
			target = new Date(targetDate.getFullYear(), targetDate.getMonth(), targetDate.getDate(), 16, 30, 0);
		}

		const sendMonth = target.getMonth() + 1;
		const sendDay = target.getDate();

		const isToday = now.getDate() === target.getDate() &&
			now.getMonth() === target.getMonth() &&
			now.getFullYear() === target.getFullYear();

		const sendBox = document.getElementById('sendBox');
		const currentTimeElement = document.createElement('p');
		currentTimeElement.id = 'currentTime';
		currentTimeElement.style.marginTop = '5px';
		currentTimeElement.style.color = 'rgba(0, 0, 0, 0.6)';
		currentTimeElement.style.letterSpacing = '-1px';
		sendBox.insertBefore(currentTimeElement, document.getElementById('countdown'));

		function updateCurrentTime() {
			const now = getKSTDate();
			const formattedNow = `${now.getFullYear()}-${(now.getMonth() + 1).toString().padStart(2, '0')}-${now.getDate().toString().padStart(2, '0')} ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}:${now.getSeconds().toString().padStart(2, '0')}`;
			document.getElementById('currentTime').innerText = `현재 시간: ${formattedNow}`;
		}

		function updateCountdown() {
			const now = getKSTDate();
			const remainingTime = target - now;

			const totalHours = Math.floor(remainingTime / (1000 * 60 * 60));
			const totalMinutes = Math.floor((remainingTime / (1000 * 60)) % 60);

			const sendDateText = isToday ? "오늘" : `${sendMonth}월 ${sendDay}일에`;

			document.getElementById('countdown').innerText =
				`${totalHours}시간 ${totalMinutes}분 이내 접수 시 ${sendDateText} 발송됩니다`;

			if (remainingTime <= 0) {
				clearInterval(countdownInterval);
				document.getElementById('countdown').innerText = "시간이 다 되었습니다!";
			}
		}

		updateCurrentTime();
		updateCountdown();
		setInterval(updateCurrentTime, 1000);
		const countdownInterval = setInterval(updateCountdown, 1000);
	}

	function copyToClipboard(text) {
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(text).then(() => {
				alert('복사되었습니다!'); // Thông báo khi copy thành công
			}).catch(err => {
				console.error('복사 실패:', err);
			});
		} else {
			const textarea = document.createElement('textarea');
			textarea.value = text;
			document.body.appendChild(textarea);
			textarea.select();
			try {
				document.execCommand('copy');
				alert('복사되었습니다!'); // Copy successful
			} catch (err) {
				console.error('복사 실패:', err);
			}
			document.body.removeChild(textarea);
		}
	}

	// startCountdown 함수를 호출하여 카운트다운 시작
	startCountdown();

</script>
