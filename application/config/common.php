<?php

/* 파일버전 */
$config['ver'] = "?v=".strtotime(date('Y-m-d H:i:s'));

/* 카카오 JSKEY */
$config['kakaoJsKey'] = '69fc19daa1d0b9675dd2f45fe481f9ec';

/* 배송 상태 state */
$config['state'] = array(
    'A' => '전체',
    'T' => '임시저장',
    'B' => '입금대기',
    'W' => '발송대기',
    'R' => '편지 출력중',
    'S' => '등기번호 입력중',
    'I' => '포장 중',
    'P' => '포장 완료',
    'F' => '발송완료', 
    'C' => '주문취소'
);

/* 주문일자 state */
$config['dateState'] = array(
    'ALL' => '전체',
//    'YESTER' => '어제',
//    'TODAY' => '오늘',
    'CHOICE' => '기간선택'
);

/* 공지사항 state */
$config['boardDateState'] = array(
    'WEEK' => '최근 1주일',
    'ALL' => '전체'
);

/* 로그확인 state */
$config['logState'] = array(
    'ACCESS' => '접속 로그',
    'LOGIN' => '로그인 로그',
);

/* 폰트 */
$config['fonts'] = array(    
    ['fontName' => 'BMJUA', 'font-family' => 'BMJUA', 'name' => '배달의민족 주아체'],
    ['fontName' => 'BMDOHYEON', 'font-family' => 'BMDOHYEON', 'name' => '배달의민족 도현체'],
    ['fontName' => 'BMYEONSUNG', 'font-family' => 'BMYEONSUNG', 'name' => '배달의민족 연성체'],
    ['fontName' => 'BMHANNAPro', 'font-family' => 'BMHANNAPro', 'name' => '배달의민족 한나체'],
    ['fontName' => 'Cafe24Oneprettynight', 'font-family' => 'Cafe24Oneprettynight', 'name' => '카페24 고운밤'],
    ['fontName' => 'Cafe24Ssurround', 'font-family' => 'Cafe24Ssurround', 'name' => '카페24 써라운드'],
    ['fontName' => 'Cafe24Danjunghae', 'font-family' => 'Cafe24Danjunghae', 'name' => '카페24 단정해'],
    ['fontName' => 'Cafe24Ohsquare', 'font-family' => 'Cafe24Ohsquare', 'name' => '카페24 아네모네'],
    ['fontName' => 'Cafe24Simplehae', 'font-family' => 'Cafe24Simplehae', 'name' => '카페24 심플한 손글씨'],
    ['fontName' => 'S-CoreDream-3Light', 'font-family' => 'S-CoreDream-3Light', 'name' => '에스코어 드림체'],
    ['fontName' => 'Godo', 'font-family' => 'Godo', 'name' => '고도체'],
    ['fontName' => 'Bareun_hipi', 'font-family' => 'Bareun_hipi', 'name' => '바른히피체'],    
    ['fontName' => 'LeeSeoyun', 'font-family' => 'LeeSeoyun', 'name' => '이서윤체'],    
//    ['fontName' => 'Jeju Myeongjo', 'font-family' => 'jejumyeongjo', 'name' => '제주명조체'],
//    ['fontName' => 'Jeju Gothic', 'font-family' => 'jejugothic', 'name' => '제주고딕체']
);

/* 손글씨 폰트 */
$config['handFonts'] = array(
	['fontName' => 'OnGleipMerry', 'font-family' => 'OnGleipMerry', 'name' => '온글잎 메리마체'],
	['fontName' => 'OnGulIpRadioPen', 'font-family' => 'OnGulIpRadioPen', 'name' => '온글잎 레디오 볼펜체'],
	['fontName' => 'HumanGothic', 'font-family' => 'Human_gothic', 'name' => '휴먼고딕'],
	['fontName' => 'HYSinMyeongJo', 'font-family' => 'HYSinMyeongJo', 'name' => 'HY신명조'],
    ['fontName' => 'Pretendard-Regular', 'font-family' => 'Pretendard-Regular', 'name' => '프리텐다드'], // END
    ['fontName' => 'IM_Hyemin-Bold', 'font-family' => 'IM_Hyemin-Bold', 'name' => 'IM혜민체'],
    ['fontName' => 'Garam', 'font-family' => 'Garam', 'name' => '가람연꽃'], // END
    ['fontName' => 'Galmetgol', 'font-family' => 'Galmetgol', 'name' => '갈맷글'], // END
    ['fontName' => 'Kangbujang', 'font-family' => 'Kangbujang', 'name' => '강부장님체'], // END
    // ['fontName' => 'Kanginhan', 'font-family' => 'Kanginhan', 'name' => '강인한 위로'], // END
    ['fontName' => 'Gothic_Goding', 'font-family' => 'Gothic_Goding', 'name' => '고딕 아니고 고딩'], // END
    // ['fontName' => 'Koreageulggol', 'font-family' => 'Koreageulggol', 'name' => '고려글꼴'], // END
    ['fontName' => 'Gomsin', 'font-family' => 'Gomsin', 'name' => '곰신체'], // END
    ['fontName' => 'Kyuri_diary', 'font-family' => 'Kyuri_diary', 'name' => '규리의 일기'], // END
    ['fontName' => 'Ownglyph_CutieStandard-Rg', 'font-family' => 'Ownglyph_CutieStandard-Rg', 'name' => '귀요미스탠다드'],
    ['fontName' => 'Geumeunbohwa', 'font-family' => 'Geumeunbohwa', 'name' => '금은보화'], // END
    ['fontName' => 'GibbemBalgeum', 'font-family' => 'GibbemBalgeum', 'name' => '기쁨밝음'], // END
    ['fontName' => 'Kimyooyee', 'font-family' => 'Kimyooyee', 'name' => '김유이체'], // END
    ['fontName' => 'Gootneaeum', 'font-family' => 'Gootneaeum', 'name' => '꽃내음'], // END
    ['fontName' => 'Ownglyph_hae_gon-Rg', 'font-family' => 'Ownglyph_hae_gon-Rg', 'name' => '꽃내체'],
    // ['fontName' => 'Ownglyph_kumhyang-Rg', 'font-family' => 'Ownglyph_kumhyang-Rg', 'name' => '금향체'],
    ['fontName' => 'I_survive', 'font-family' => 'I_survive', 'name' => '나는 이겨낸다'], // END
    ['fontName' => 'Treegarden', 'font-family' => 'Treegarden', 'name' => '나무정원'], // END
    ['fontName' => 'My_wife_writing', 'font-family' => 'My_wife_writing', 'name' => '나의 아내 손글씨'], // END
//    ['fontName' => 'Nanum Brush Script', 'font-family' => 'nanumbrushscript', 'name' => '네이버 나눔손글씨 붓'],
//    ['fontName' => 'Nanum Pen Script', 'font-family' => 'nanumpenscript', 'name' => '네이버 나눔손글씨 펜'],
    ['fontName' => 'Hardworking_donghee', 'font-family' => 'Hardworking_donghee', 'name' => '노력하는 동희'], // END
    ['fontName' => 'Ownglyph_noocar-Rg', 'font-family' => 'Ownglyph_noocar-Rg', 'name' => '누카'],
    ['fontName' => 'Ownglyph_New_Bohyun-Rg', 'font-family' => 'Ownglyph_New_Bohyun-Rg', 'name' => '뉴보현'],
    ['fontName' => 'Ownglyph_NewMoogung-Rg', 'font-family' => 'Ownglyph_NewMoogung-Rg', 'name' => '뉴무궁체'],
    ['fontName' => 'Restart', 'font-family' => 'Restart', 'name' => '다시 시작해'], // END
    ['fontName' => 'Dajin', 'font-family' => 'Dajin', 'name' => '다진체'], // END
    ['fontName' => 'Dache_love', 'font-family' => 'Dache_love', 'name' => '다채사랑'], // END
    ['fontName' => 'Daheng', 'font-family' => 'Daheng', 'name' => '다행체'], // END
    ['fontName' => 'Orbit_of_moon', 'font-family' => 'Orbit_of_moon', 'name' => '달의궤도'], // END
    ['fontName' => 'Deagwang_mirror', 'font-family' => 'Deagwang_mirror', 'name' => '대광유리'], // END
    ['fontName' => 'Korea_hero', 'font-family' => 'Korea_hero', 'name' => '대한민국 열사체'], // END
    ['fontName' => 'Fairytale_ddobak', 'font-family' => 'Fairytale_ddobak', 'name' => '동화또박'], // END
    ['fontName' => 'Round_destiny', 'font-family' => 'Round_destiny', 'name' => '둥근인연'], // END
    ['fontName' => 'Warm_farewell', 'font-family' => 'Warm_farewell', 'name' => '따뜻한 작별'], // END
    ['fontName' => 'Ddakdandan', 'font-family' => 'Ddakdandan', 'name' => '따악단단'], // END
    ['fontName' => 'mom_to_daughter', 'font-family' => 'mom_to_daughter', 'name' => '딸에게 엄마가'], // END
    ['fontName' => 'Ownglyph_Didoni-Rg', 'font-family' => 'Ownglyph_Didoni-Rg', 'name' => '디도니'],
    ['fontName' => 'Mago', 'font-family' => 'Mago', 'name' => '마고체'], // END
    ['fontName' => 'Ownglyph_Clean_Fragrance-Rg', 'font-family' => 'Ownglyph_Clean_Fragrance-Rg', 'name' => '맑은향기'],
    ['fontName' => 'Yammy', 'font-family' => 'Yammy', 'name' => '맛있는체'], // END
//    ['fontName' => 'Mongdol', 'font-family' => 'Mongdol', 'name' => '몽돌'],
    ['fontName' => 'Mugunghwa', 'font-family' => 'Mugunghwa', 'name' => '무궁화'], // END
    ['fontName' => 'Mujinjang', 'font-family' => 'Mujinjang', 'name' => '무진장체'],   // END  
    ['fontName' => 'Mini_handwriting', 'font-family' => 'Mini_handwriting', 'name' => '미니 손글씨'], // END 
    ['fontName' => 'Future_tree', 'font-family' => 'Future_tree', 'name' => '미래나무'], // END 
    ['fontName' => 'Bareun_mental', 'font-family' => 'Bareun_mental', 'name' => '바른정신'], // END 
    ['fontName' => 'Bareun_hipi', 'font-family' => 'Bareun_hipi', 'name' => '바른히피'],// END 
    ['fontName' => 'Shining_star', 'font-family' => 'Shining_star', 'name' => '반짝반짝 별'],// END 
    ['fontName' => 'Beeunhye', 'font-family' => 'Beeunhye', 'name' => '배은혜체'],// END 
    ['fontName' => 'White_angel', 'font-family' => 'White_angel', 'name' => '백의의 천사'],// END 
    ['fontName' => 'Bud_tree', 'font-family' => 'Bud_tree', 'name' => '버드나무'],// END 
    ['fontName' => 'Bumsom', 'font-family' => 'Bumsom', 'name' => '범솜체'],// END 
    // ['fontName' => 'Ownglyph_BERRY_RW_Rg', 'font-family' => 'Ownglyph_BERRY_RW_Rg', 'name' => '베리려원'],
    // ['fontName' => 'Ownglyph_BucheonGrandma_Rg', 'font-family' => 'Ownglyph_BucheonGrandma_Rg', 'name' => '부천할머니'],
//    ['fontName' => 'Bujangnim_nunchi', 'font-family' => 'Bujangnim_nunchi', 'name' => '부장님 눈치체'],
    ['fontName' => 'Polar_Star', 'font-family' => 'Polar_Star', 'name' => '북극성'],// END 
    ['fontName' => 'SlowSlow', 'font-family' => 'SlowSlow', 'name' => '느릿느릿체'],// END 
    ['fontName' => 'Bisang', 'font-family' => 'Bisang', 'name' => '비상체'],// END 
    ['fontName' => 'Bbang_gunimom', 'font-family' => 'Bbang_gunimom', 'name' => '빵구니맘 손글씨'],// END 
    ['fontName' => 'Love_son', 'font-family' => 'Love_son', 'name' => '사랑해 아들'],// END 
    ['fontName' => 'Sanghea_chanmi', 'font-family' => 'Sanghea_chanmi', 'name' => '상해찬미체'],   // END  
    ['fontName' => 'Sungsil', 'font-family' => 'Sungsil', 'name' => '성실체'],// END
    ['fontName' => 'Ownglyph_seonsiwoo-Rg', 'font-family' => 'Ownglyph_seonsiwoo-Rg', 'name' => '선시우체'],
    ['fontName' => 'Ownglyph_Seolyoung-Rg', 'font-family' => 'Ownglyph_Seolyoung-Rg', 'name' => '설영체'],
    ['fontName' => 'Ownglyph_Sunghee-Rg', 'font-family' => 'Ownglyph_Sunghee-Rg', 'name' => '성희'],
    ['fontName' => 'Global_Hangul', 'font-family' => 'Global_Hangul', 'name' => '세계적인 한글'],// END
    ['fontName' => 'Se-a', 'font-family' => 'Se-a', 'name' => '세아체'],// END
    ['fontName' => 'Se-hwa', 'font-family' => 'Se-hwa', 'name' => '세화체'],// END
    ['fontName' => 'Ownglyph_soish-Rg', 'font-family' => 'Ownglyph_soish-Rg', 'name' => '소망체'],
    ['fontName' => 'Somi', 'font-family' => 'Somi', 'name' => '소미체'],   // END
    ['fontName' => 'Handletter', 'font-family' => 'Handletter', 'name' => '손편지체'],// END
    ['fontName' => 'Ownglyph_ktr-Rg', 'font-family' => 'Ownglyph_ktr-Rg', 'name' => '수경체'],
    ['fontName' => 'Shy_college_student', 'font-family' => 'Shy_college_student', 'name' => '수줍은 대학생'],// END
    ['fontName' => 'Cute_siu', 'font-family' => 'Cute_siu', 'name' => '시우 귀여워'],// END
    ['fontName' => 'Newly_married', 'font-family' => 'Newly_married', 'name' => '신혼부부'],// END
    ['fontName' => 'Baby_love', 'font-family' => 'Baby_love', 'name' => '아기사랑체'],// END
    ['fontName' => 'Flower_tree', 'font-family' => 'Flower_tree', 'name' => '아름드리 꽃나무'],// END
    ['fontName' => 'Father_handwriting', 'font-family' => 'Father_handwriting', 'name' => '아빠글씨'],// END
    ['fontName' => 'Father_loveletter', 'font-family' => 'Father_loveletter', 'name' => '아빠의 연애편지'],// END
    ['fontName' => 'Ainmom', 'font-family' => 'Ainmom', 'name' => '아인맘 손글씨'],
    ['fontName' => 'Ownglyph_Ayin-Rg', 'font-family' => 'Ownglyph_Ayin-Rg', 'name' => '아인이'],
    ['fontName' => 'Jayoo', 'font-family' => 'Jayoo', 'name' => '아줌마 자유'],
    ['fontName' => 'Anssang', 'font-family' => 'Anssang', 'name' => '안쌍체'],
    ['fontName' => 'Amsterdam', 'font-family' => 'Amsterdam', 'name' => '암스테르담'],
    ['fontName' => 'Kimjuim', 'font-family' => 'Kimjuim', 'name' => '야근하는 김주임'],
    ['fontName' => 'Beakeumrye', 'font-family' => 'Beakeumrye', 'name' => '야채장수 백금례'],
    ['fontName' => 'Love_mom', 'font-family' => 'Love_mom', 'name' => '엄마사랑'],
    ['fontName' => 'Unggungqui', 'font-family' => 'Unggungqui', 'name' => '엉겅퀴체'],
    ['fontName' => 'Summer_letter', 'font-family' => 'Summer_letter', 'name' => '여름글씨'],
    ['fontName' => 'Yeonji', 'font-family' => 'Yeonji', 'name' => '연지체'],
    ['fontName' => 'Syning_19', 'font-family' => 'Syning_19', 'name' => '열아홉의 반짝임'],
    ['fontName' => 'Hardworking', 'font-family' => 'Hardworking', 'name' => '열일체'],
    ['fontName' => 'Ownglyph_youngguen-Rg', 'font-family' => 'Ownglyph_youngguen-Rg', 'name' => '영근'],
    ['fontName' => 'Yedang', 'font-family' => 'Yedang', 'name' => '예당체'],
    ['fontName' => 'Mingyung', 'font-family' => 'Mingyung', 'name' => '예쁜 민경체'],
    ['fontName' => 'Ownglyph_Yezi927-Rg', 'font-family' => 'Ownglyph_Yezi927-Rg', 'name' => '예지927'],
    ['fontName' => 'Okbi', 'font-family' => 'Okbi', 'name' => '옥비체'],
    ['fontName' => 'Ownglyph_Summerinmyhouse-Rg', 'font-family' => 'Ownglyph_Summerinmyhouse-Rg', 'name' => '우리집여름이체'],
    ['fontName' => 'Wild', 'font-family' => 'Wild', 'name' => '와일드'],
    ['fontName' => 'Grandma_wring', 'font-family' => 'Grandma_wring', 'name' => '외할머니글씨'],
    ['fontName' => 'Pretty_Left_handed', 'font-family' => 'Pretty_Left_handed', 'name' => '왼손잡이도 예뻐'],
    ['fontName' => 'Daughter_handwriting', 'font-family' => 'Daughter_handwriting', 'name' => '우리딸 손글씨'],
    ['fontName' => 'Yuni_ddingddang', 'font-family' => 'Yuni_ddingddang', 'name' => '유니 띵땅띵땅'],
    ['fontName' => 'Ownglyph_Yoonji-Rg', 'font-family' => 'Ownglyph_Yoonji-Rg', 'name' => '윤지는유니'],
    ['fontName' => 'Ownglyph_Lee_Jee_Bold-Rg', 'font-family' => 'Ownglyph_Lee_Jee_Bold-Rg', 'name' => '이지 볼드'],
    ['fontName' => 'Ownglyph_Eunyeong-Rg', 'font-family' => 'Ownglyph_Eunyeong-Rg', 'name' => '은영체'],
    ['fontName' => 'Meaningful_hanguel', 'font-family' => 'Meaningful_hanguel', 'name' => '의미있는 한글'],
    ['fontName' => 'Pride_jiu', 'font-family' => 'Pride_jiu', 'name' => '자부심지우'],
    ['fontName' => 'Doing_well', 'font-family' => 'Doing_well', 'name' => '잘하고 있어'],
//    ['fontName' => 'Rose', 'font-family' => 'Rose', 'name' => '장미체'],
    ['fontName' => 'Dot_font', 'font-family' => 'Dot_font', 'name' => '점꼴체'],
    ['fontName' => 'Ownglyph_Mrs_Jung-Rg', 'font-family' => 'Ownglyph_Mrs_Jung-Rg', 'name' => '정여사'],    
    ['fontName' => 'Jungeun', 'font-family' => 'Jungeun', 'name' => '정은체'],
    ['fontName' => 'Ownglyph_James-Rg', 'font-family' => 'Ownglyph_James-Rg', 'name' => '장칼체'],   
    ['fontName' => 'Ownglyph_JNBexpress-Rg', 'font-family' => 'Ownglyph_JNBexpress-Rg', 'name' => '제이엔비'],
    ['fontName' => 'Middleschool_student', 'font-family' => 'Middleschool_student', 'name' => '중학생'],
    ['fontName' => 'Parkgyunga', 'font-family' => 'Parkgyunga', 'name' => '진주 박경아체'],
    ['fontName' => 'Ownglyph_Jinpall_bold-Rg', 'font-family' => 'Ownglyph_Jinpall_bold-Rg', 'name' => '진팔이 볼드'],
    ['fontName' => 'Chulpil_writing', 'font-family' => 'Chulpil_writing', 'name' => '철필글씨'],
    ['fontName' => 'Choding_hope', 'font-family' => 'Choding_hope', 'name' => '초딩희망'],
    ['fontName' => 'Kalguksu', 'font-family' => 'Kalguksu', 'name' => '칼국수'],
    ['fontName' => 'Ownglyph_Kundo-Rg', 'font-family' => 'Ownglyph_Kundo-Rg', 'name' => '쿤도체'],
//    ['fontName' => 'Coco', 'font-family' => 'Coco', 'name' => '코코체'],
    ['fontName' => 'Become_one', 'font-family' => 'Become_one', 'name' => '하나되어 손글씨'],
    ['fontName' => 'Hana_handwriting', 'font-family' => 'Hana_handwriting', 'name' => '하나손글씨'],
    ['fontName' => 'Haram', 'font-family' => 'Haram', 'name' => '하람체'],
    ['fontName' => 'Hanyoon', 'font-family' => 'Hanyoon', 'name' => '한윤체'],
    // ['fontName' => 'Ownglyph_HanJiwoo-Rg', 'font-family' => 'Ownglyph_HanJiwoo-Rg', 'name' => '한지우체'],
    ['fontName' => 'Grandpa_sharing', 'font-family' => 'Grandpa_sharing', 'name' => '할아버지의나눔'],
    ['fontName' => 'Happy_dobi', 'font-family' => 'Happy_dobi', 'name' => '행복한 도비'],
    ['fontName' => 'Hyukee', 'font-family' => 'Hyukee', 'name' => '혁이체'],
    ['fontName' => 'Hyejun', 'font-family' => 'Hyejun', 'name' => '혜준체'],
    ['fontName' => 'Fighting_hyonam', 'font-family' => 'Fighting_hyonam', 'name' => '효남 늘 화이팅'],
    ['fontName' => 'Ownglyph_hyorin-Rg', 'font-family' => 'Ownglyph_hyorin-Rg', 'name' => '효린체'],
    ['fontName' => 'Ownglyph_hwanseung-Rg', 'font-family' => 'Ownglyph_hwanseung-Rg', 'name' => '환승체'],
    ['fontName' => 'Hope_nuri', 'font-family' => 'Hope_nuri', 'name' => '희망누리'],
    ['fontName' => 'White_kkorisuri', 'font-family' => 'White_kkorisuri', 'name' => '흰꼬리수리'],
    ['fontName' => 'Saying_tobe_strong', 'font-family' => 'Saying_tobe_strong', 'name' => '힘내라는 말보단']
);

/* 전국 구치소/교도소/소년원 */
$config['prisonType'] = array('서울/경기', '강원도', '충청도', '전라도', '경상도', '제주도');
$config['prison'] = array(    
    array('region' => '서울/경기', 'name' => '서울구치소', 'post' => 15829, 'address' => '경기도 군포우체국 사서함', 'addressDetail' => '20호'),
    array('region' => '서울/경기', 'name' => '서울동부구치소(구)성동구치소', 'post' => '05661', 'address' => '서울시 송파우체국 사서함', 'addressDetail' => '177호'),
    array('region' => '서울/경기', 'name' => '서울남부구치소', 'post' => '08365', 'address' => '서울시 구로우체국 사서함', 'addressDetail' => '164호'),
    array('region' => '서울/경기', 'name' => '서울남부교도소', 'post' => '08365', 'address' => '서울시 구로우체국 사서함', 'addressDetail' => '165호'),
    array('region' => '서울/경기', 'name' => '인천구치소', 'post' => 21552, 'address' => '인천광역시 남인천우체국사서함', 'addressDetail' => '343호'),
    array('region' => '서울/경기', 'name' => '수원구치소', 'post' => 16326, 'address' => '경기도 수원우체국 사서함', 'addressDetail' => '17호'),
    array('region' => '서울/경기', 'name' => '수원구치소평택지소', 'post' => 17895, 'address' => '경기도 평택우체국 사서함', 'addressDetail' => '6호'),
    array('region' => '서울/경기', 'name' => '안양교도소', 'post' => 14047, 'address' => '경기도 안양우체국 사서함', 'addressDetail' => '101호'),
    array('region' => '서울/경기', 'name' => '의정부교도소', 'post' => 11778, 'address' => '경기도 의정부우체국 사서함', 'addressDetail' => '99호'),
    array('region' => '서울/경기', 'name' => '여주교도소', 'post' => 12627, 'address' => '경기도 여주우체국사서함', 'addressDetail' => '30호'),
    array('region' => '서울/경기', 'name' => '화성직업훈련교도소', 'post' => 18270, 'address' => '경기도 화성시 남양읍 화성우체국 사서함', 'addressDetail' => '3호'),
    array('region' => '서울/경기', 'name' => '소망교도소(민영)', 'post' => 12627, 'address' => '경기도 여주시 여주우체국 사서함', 'addressDetail' => '23호'),
    array('region' => '서울/경기', 'name' => '국군교도소', 'post' => 17414, 'address' => '경기도 이천시 장호원읍 경충대로 597번길 57-128 사서함', 'addressDetail' => '900-10호 수용중대'),
    array('region' => '서울/경기', 'name' => '서울소년분류심사원', 'post' => 14122, 'address' => '경기 안양시 동안구 경수대로 500 (호계동, 서울소년분류심사원)', 'addressDetail' => ''),
    array('region' => '서울/경기', 'name' => '고봉중고등학교(서울소년원)', 'post' => 16075, 'address' => '경기 의왕시 고산로 87 (고천동)', 'addressDetail' => ''),
    array('region' => '서울/경기', 'name' => '정심여자중고등학교(안양소년원)', 'post' => 13910, 'address' => '경기 안양시 만안구 삼막로96번길 11 (석수동)', 'addressDetail' => ''),
    
    array('region' => '강원도', 'name' => '춘천교도소', 'post' => 24335, 'address' => '강원도 춘천시 춘천우체국 사서함', 'addressDetail' => '69호'),
    array('region' => '강원도', 'name' => '원주교도소', 'post' => 26485, 'address' => '강원도 원주우체국 사서함', 'addressDetail' => '87호'),
    array('region' => '강원도', 'name' => '강릉교도소', 'post' => 25534, 'address' => '강릉시 강릉우체국 사서함', 'addressDetail' => '43호'),
    array('region' => '강원도', 'name' => '영월교도소', 'post' => 26233, 'address' => '강원도 영월군 영월우체국 사서함', 'addressDetail' => '2호'),
    array('region' => '강원도', 'name' => '강원북부교도소', 'post' => 24862, 'address' => '강원도 속초시 속초우체국 사서함', 'addressDetail' => '2호'),
    array('region' => '강원도', 'name' => '신촌정보통신학교(춘천소년원)', 'post' => 24407, 'address' => '강원 춘천시 동내면 장안길 51 (신촌리, 신촌정보통신학교)', 'addressDetail' => ''),
    
    array('region' => '충청도', 'name' => '대전교도소', 'post' => 34186, 'address' => '대전시 유성우체국 사서함', 'addressDetail' => '136호'),
    array('region' => '충청도', 'name' => '대전교도소논산지소', 'post' => 32927, 'address' => '충남 논산시 성동우체국 사서함', 'addressDetail' => '1호'),
    array('region' => '충청도', 'name' => '천안교도소', 'post' => 31016, 'address' => '충남 천안시 서북구 성환읍 성환우체국 사서함', 'addressDetail' => '20호'),
    array('region' => '충청도', 'name' => '천안개방교도소', 'post' => 31158, 'address' => '충남 천안시 천안우체국 사서함', 'addressDetail' => '36호'),
    array('region' => '충청도', 'name' => '청주교도소', 'post' => 28426, 'address' => '충북 청주시 서청주우체국 사서함', 'addressDetail' => '100호'),
    array('region' => '충청도', 'name' => '청주여자교도소', 'post' => 28426, 'address' => '충북 청주시 서청주우체국 사서함', 'addressDetail' => '145호'),
    array('region' => '충청도', 'name' => '공주교도소', 'post' => 32546, 'address' => '충남 공주우체국 사서함', 'addressDetail' => '13호'),
    array('region' => '충청도', 'name' => '충주구치소', 'post' => 27313, 'address' => '충북 충주시 엄정우체국 사서함', 'addressDetail' => '1호'),
    array('region' => '충청도', 'name' => '홍성교도소', 'post' => 32247, 'address' => '충남 홍성우체국 사서함', 'addressDetail' => '9호'),
    array('region' => '충청도', 'name' => '홍성교도소서산지소', 'post' => 31930, 'address' => '충남 서산시 성연우체국 사서함', 'addressDetail' => '1호'),
    array('region' => '충청도', 'name' => '미평여자학교(청주소년원)', 'post' => 28632, 'address' => '충북 청주시 서원구 남지로41번길 23 (미평동, 미평고등학교)', 'addressDetail' => ''),
    array('region' => '충청도', 'name' => '대산학교(대전소년원)', 'post' => 34699, 'address' => '대전 동구 산내로 1398-41 (대성동)', 'addressDetail' => ''),
    
    array('region' => '전라도', 'name' => '광주교도소', 'post' => 61057, 'address' => '광주시 북광주우체국 사서함', 'addressDetail' => '63호'),
    array('region' => '전라도', 'name' => '전주교도소', 'post' => 54966, 'address' => '전북 전주우체국 사서함', 'addressDetail' => '72호'),
    array('region' => '전라도', 'name' => '순천교도소', 'post' => 57987, 'address' => '전남 순천우체국 사서함', 'addressDetail' => '9호'),
    array('region' => '전라도', 'name' => '목포교도소', 'post' => 58574, 'address' => '전남 무안 일로우체국 사서함', 'addressDetail' => '1호'),
    array('region' => '전라도', 'name' => '군산교도소', 'post' => 54025, 'address' => '전북 군산우체국 사서함', 'addressDetail' => '10호'),
    array('region' => '전라도', 'name' => '장흥교도소', 'post' => 59328, 'address' => '전남 장흥군 장흥우체국 사서함', 'addressDetail' => '1호'),
    array('region' => '전라도', 'name' => '해남교도소', 'post' => 59027, 'address' => '전남 해남우체국 사서함', 'addressDetail' => '6호'),
    array('region' => '전라도', 'name' => '정읍교도소', 'post' => 56160, 'address' => '전북 정읍우체국 사서함', 'addressDetail' => '1호'),
    array('region' => '전라도', 'name' => '송천중고등학교(전주소년원)', 'post' => 55145, 'address' => '전북 전주시 덕진구 과학로 45-25 (송천동2가)', 'addressDetail' => ''),
    array('region' => '전라도', 'name' => '고룡정보산업학교(광주소년원)', 'post' => 62207, 'address' => '광주 광산구 임곡로 273-3 (고룡동)', 'addressDetail' => ''),
    
    array('region' => '경상도', 'name' => '부산구치소', 'post' => 46974, 'address' => '부산시 사상우체국 사서함', 'addressDetail' => '58호'),
    array('region' => '경상도', 'name' => '부산교도소', 'post' => 46700, 'address' => '부산시 강서우체국 사서함', 'addressDetail' => '50호'),
    array('region' => '경상도', 'name' => '창원교도소', 'post' => 51304, 'address' => '경남 마산우체국 사서함', 'addressDetail' => '7호'),
    array('region' => '경상도', 'name' => '진주교도소', 'post' => 52684, 'address' => '경남 진주시 진주우체국 사서함', 'addressDetail' => '68호'),
    array('region' => '경상도', 'name' => '포항교도소', 'post' => 37542, 'address' => '경북 포항시 흥해우체국 사서함', 'addressDetail' => '2호'),
    array('region' => '경상도', 'name' => '대구구치소', 'post' => 42123, 'address' => '대구시 수성우체국 사서함', 'addressDetail' => '48호'),
    array('region' => '경상도', 'name' => '(신)대구교도소', 'post' => 42620, 'address' => '대구광역시 성서우체국 사서함', 'addressDetail' => '7호'),
    // array('region' => '경상도', 'name' => '경북직업훈련교도소', 'post' => 37409, 'address' => '경북 청송군 진보우체국 사서함', 'addressDetail' => '2호'),
    // array('region' => '경상도', 'name' => '경북북부제1교도소', 'post' => 37409, 'address' => '경북 청송군 진보면 진보우체국사서함', 'addressDetail' => '1호'),
    // array('region' => '경상도', 'name' => '경북북부제2교도소', 'post' => 37409, 'address' => '경북 청송군 진보우체국 사서함', 'addressDetail' => '5호'),
    // array('region' => '경상도', 'name' => '경북북부제3교도소', 'post' => 37409, 'address' => '경북 청송군 진보우체국 사서함', 'addressDetail' => '3호'),
    array('region' => '경상도', 'name' => '경북직업훈련교도소', 'post' => 37402, 'address' => '경상북도 청송군 진보면 양정길 231', 'addressDetail' => '경북직업훈련교도소'),
    array('region' => '경상도', 'name' => '경북북부제1교도소', 'post' => 37402, 'address' => '경상북도 청송군 진보면 양정길 231', 'addressDetail' => '경북북부제1교도소'),
    array('region' => '경상도', 'name' => '경북북부제2교도소', 'post' => 37402, 'address' => '경상북도 청송군 진보면 양정길 110', 'addressDetail' => '경북북부제2교도소'),
    array('region' => '경상도', 'name' => '경북북부제3교도소', 'post' => 37402, 'address' => '경상북도 청송군 진보면 양정길 231', 'addressDetail' => '경북북부제3교도소'),
    array('region' => '경상도', 'name' => '안동교도소', 'post' => 36621, 'address' => '경북 안동시 풍산읍 안동풍산우체국 사서함', 'addressDetail' => '1호'),
    array('region' => '경상도', 'name' => '김천소년교도소', 'post' => 39590, 'address' => '경북 김천시 김천우체국 사서함', 'addressDetail' => '12호'),
    array('region' => '경상도', 'name' => '울산구치소', 'post' => 44974, 'address' => '울산시 온양우체국 사서함', 'addressDetail' => '1호'),
    array('region' => '경상도', 'name' => '경주교도소', 'post' => 38153, 'address' => '경북 경주시 경주우체국 사서함', 'addressDetail' => '45호'),
    array('region' => '경상도', 'name' => '통영구치소', 'post' => 53043, 'address' => '경남 통영시 통영우체국 사서함', 'addressDetail' => '17호'),
    array('region' => '경상도', 'name' => '밀양구치소', 'post' => 50445, 'address' => '경남 밀양시 밀양우체국 사서함', 'addressDetail' => '8호'),
    array('region' => '경상도', 'name' => '상주교도소', 'post' => 37190, 'address' => '경북 상주시 상주우체국 사서함', 'addressDetail' => '20호'),
    array('region' => '경상도', 'name' => '읍내정보통신학교(대구소년원)', 'post' => 41442, 'address' => '대구 북구 칠곡중앙대로99길 12 (읍내동)', 'addressDetail' => ''),
    array('region' => '경상도', 'name' => '오륜정보산업학교(부산소년원)', 'post' => 46255, 'address' => '부산 금정구 오륜대로126번길 62 (오륜동)', 'addressDetail' => ''),
    array('region' => '경상도', 'name' => '거창구치소', 'post' => 50132, 'address' => '경상남도 거창군 거창우체국 사서함', 'addressDetail' => '1호'),    
    
    array('region' => '제주도', 'name' => '제주교도소', 'post' => 63166, 'address' => '제주도 제주우체국 사서함', 'addressDetail' => '161호'),
    array('region' => '제주도', 'name' => '한길정보통신학교(제주소년원)', 'post' => 63037, 'address' => '제주특별자치도 제주시 애월읍 장소로 515 (소길리)', 'addressDetail' => '')        
);

/* 훈련소 */
$config['armyType'] = array('육군', '해군', '공군', '해병대');
$config['army'] = array(
    array('region' => '육군', 'name' => '논산훈련소 23연대', 'post' => 33012, 'address' => '충남 논산시 연무읍 득안대로 504번길 사서함', 'addressDetail' => '76-8호 23연대'),
    array('region' => '육군', 'name' => '논산훈련소 25연대', 'post' => 33012, 'address' => '충남 논산시 연무읍 득안대로 504번길 사서함', 'addressDetail' => '76-9호 25연대'),
    array('region' => '육군', 'name' => '논산훈련소 26연대', 'post' => 33012, 'address' => '충남 논산시 연무읍 득안대로 504번길 사서함', 'addressDetail' => '76-10호 26연대'),
    array('region' => '육군', 'name' => '논산훈련소 27연대', 'post' => 33012, 'address' => '충남 논산시 연무읍 득안대로 504번길 사서함', 'addressDetail' => '76-11호 27연대'),
    array('region' => '육군', 'name' => '논산훈련소 28연대', 'post' => 33012, 'address' => '충남 논산시 연무읍 득안대로 504번길 사서함', 'addressDetail' => '76-12호 28연대'),
    array('region' => '육군', 'name' => '논산훈련소 29연대', 'post' => 33012, 'address' => '충남 논산시 연무읍 득안대로 504번길 사서함', 'addressDetail' => '76-13호 29연대'),
    array('region' => '육군', 'name' => '논산훈련소 30연대', 'post' => 33012, 'address' => '충남 논산시 연무읍 득안대로 504번길 사서함', 'addressDetail' => '76-14호 30연대'),
    array('region' => '육군', 'name' => '1사단 전진부대 신병교육대', 'post' => 10948, 'address' => '경기도 파주시 광탄면 사서함', 'addressDetail' => '109-11호'),
    array('region' => '육군', 'name' => '2사단 노도부대 신병교육대', 'post' => 24518, 'address' => '강원도 양구군 남면 정중앙로 사서함', 'addressDetail' => '108-26호'),
    array('region' => '육군', 'name' => '3사단 백골부대 신병교육대', 'post' => 24062, 'address' => '강원도 철원군 서면 금강로 76-28 사서함', 'addressDetail' => '107-17-1호'),
    array('region' => '육군', 'name' => '5사단 열쇠부대 신병교육대', 'post' => 11021, 'address' => '경기도 연천군 청산면 청창로 사서함', 'addressDetail' => '106-16호'),
    array('region' => '육군', 'name' => '6사단 청성부대 신병교육대', 'post' => 11101, 'address' => '강원도 철원군 동송읍 담터길 사서함', 'addressDetail' => '105-100호'),
    array('region' => '육군', 'name' => '7사단 칠성부대 신병교육대', 'post' => 24108, 'address' => '강원도 화천군 화천읍 한묵령로 사서함', 'addressDetail' => '104-34호'),
    array('region' => '육군', 'name' => '8사단 오뚜기부대 신병교육대', 'post' => 11112, 'address' => '경기도 포천시 이동면 성장로 사서함', 'addressDetail' => '103-24호'),
    array('region' => '육군', 'name' => '9사단 백마부대 신병교육대', 'post' => 10319, 'address' => '경기도 고양시 일산동구 풍동사서함', 'addressDetail' => '110-15호'),
    array('region' => '육군', 'name' => '11사단 화랑부대 신병교육대', 'post' => 25115, 'address' => '강원도 홍천군 북방면 상화계리 사서함', 'addressDetail' => '101-12호'),
    array('region' => '육군', 'name' => '12사단 을지부대 신병교육대', 'post' => 24621, 'address' => '강원도 인제군 북면 월학리 사서함', 'addressDetail' => '100-35호'),
    array('region' => '육군', 'name' => '15사단 승리부대 신병교육대', 'post' => 24148, 'address' => '강원도 화천군 상서면 신월동로 사서함', 'addressDetail' => '99-26호'),
    array('region' => '육군', 'name' => '17사단 번개부대 신병교육대', 'post' => 21450, 'address' => '인천시 부평구 구산동 사서함', 'addressDetail' => '317-15호'),
    array('region' => '육군', 'name' => '20사단 결전부대 신병교육대', 'post' => 12563, 'address' => '경기도 양평군 양평읍 덕평리 재맛길 사서함', 'addressDetail' => '91-9호'),
    array('region' => '육군', 'name' => '21사단 백두산부대 신병교육대', 'post' => 24513, 'address' => '강원도 양구군 방산면 송현1리 사서함', 'addressDetail' => '97-26호'),
    array('region' => '육군', 'name' => '22사단 율곡부대 신병교육대', 'post' => 24753, 'address' => '강원도 고성군 토성면 사서함', 'addressDetail' => '71-10호'),
    array('region' => '육군', 'name' => '23사단 철벽부대 신병교육대', 'post' => 25909, 'address' => '강원도 삼척시 새천년도로 사서함', 'addressDetail' => '73-17호'),
    array('region' => '육군', 'name' => '25사단 비룡부대 신병교육대', 'post' => 11400, 'address' => '경기도 양주시 남면 사서함', 'addressDetail' => '95-23호'),
    array('region' => '육군', 'name' => '26사단 불무리부대 신병교육대', 'post' => 11506, 'address' => '경기도 양주시 백석읍 부흥로 사서함', 'addressDetail' => '94-13호'),
    array('region' => '육군', 'name' => '27사단 이기자부대 신병교육대', 'post' => 24161, 'address' => '강원도 화천군 사내면 수밀리길 사서함', 'addressDetail' => '93-12호'),
    array('region' => '육군', 'name' => '28사단 태풍부대 신병교육대', 'post' => 11335, 'address' => '경기도 파주시 적성면 율곡로 사서함', 'addressDetail' => '92-28호'),
    array('region' => '육군', 'name' => '30사단 필승부대 신병교육대', 'post' => 10498, 'address' => '고양시 덕양구 덕양우체국 사서함', 'addressDetail' => '86-25호'),
    array('region' => '육군', 'name' => '31사단 충장부대 신병교육대', 'post' => 61048, 'address' => '광주광역시 북구 사서함', 'addressDetail' => '85-14호'),
    array('region' => '육군', 'name' => '32사단 백룡부대 신병교육대', 'post' => 30086, 'address' => '세종특별자치시 금남면 사서함', 'addressDetail' => '119-14호'),
    array('region' => '육군', 'name' => '35사단 충경부대 신병교육대', 'post' => 55929, 'address' => '전북 임실군 임실읍 감천로 사서함', 'addressDetail' => '89-314호'),
    array('region' => '육군', 'name' => '36사단 백호부대 신병교육대', 'post' => 26308, 'address' => '강원도 원주시 소초면 치악로 사서함', 'addressDetail' => '87-12호'),
    array('region' => '육군', 'name' => '37사단 충용부대 신병교육대', 'post' => 27913, 'address' => '충북 증평군 증평읍 사서함', 'addressDetail' => '82-10호'),
    array('region' => '육군', 'name' => '39사단 충무부대 신병교육대', 'post' => 52064, 'address' => '경상남도 함안군 군북면 사서함', 'addressDetail' => '80-5호'),
    array('region' => '육군', 'name' => '50사단 강철부대 신병교육대', 'post' => 41407, 'address' => '대구광역시 북구사서함', 'addressDetail' => '79-11호'),
    array('region' => '육군', 'name' => '51사단 전승부대 신병교육대', 'post' => 18288, 'address' => '경기도 화성시 매송면 화성로 사서함', 'addressDetail' => '14-20호'),
    array('region' => '육군', 'name' => '53사단 충렬부대 신병교육대', 'post' => 48067, 'address' => '부산광역시 해운대구 좌동 사서함', 'addressDetail' => '321-16호'),
    array('region' => '육군', 'name' => '55사단 봉화부대 신병교육대', 'post' => 17019, 'address' => '경기도 용인시 용인우체국 사서함', 'addressDetail' => '3호'),
    
    array('region' => '해군', 'name' => '해군사령부', 'post' => 51686, 'address' => '경상남도 창원시 진해구 진희로111 사서함', 'addressDetail' => '211호'),
    array('region' => '해군', 'name' => '기초군사교육단-부사관후보생', 'post' => 51686, 'address' => '경상남도 창원시 진해구 진희로 111 사서함', 'addressDetail' => '211-2-2호'),
    array('region' => '해군', 'name' => '1대대 기초군사교육단-훈련병,의경', 'post' => 51686, 'address' => '경상남도 창원시 진해구 진희로 111 사서함', 'addressDetail' => '211-2-2호'),
    array('region' => '해군', 'name' => '2대대 기초군사교육단-훈련병,의경', 'post' => 51686, 'address' => '경상남도 창원시 진해구 진희로 111 사서함', 'addressDetail' => '211-2-2호'),
    array('region' => '해군', 'name' => '기초군사교육단-승근/산기능', 'post' => 51686, 'address' => '경상남도 창원시 진해구 진희로 111 사서함', 'addressDetail' => '211-2-2호'),
    array('region' => '해군', 'name' => '교육사령부', 'post' => 51686, 'address' => '경상남도 창원시 진해구 진희로111 사서함', 'addressDetail' => '211호'),
    array('region' => '해군', 'name' => '전투병과학교', 'post' => 51686, 'address' => '경상남도 창원시 진해구 진희로 111 사서함', 'addressDetail' => '211-3-2호'),
    array('region' => '해군', 'name' => '기술행정학교', 'post' => 51698, 'address' => '경상남도 창원시 진해구 중원로1 사서함', 'addressDetail' => '605-1-9호'),
    array('region' => '해군', 'name' => '정보통신학교', 'post' => 51698, 'address' => '경상남도 창원시 진해구 중원로1 사서함', 'addressDetail' => '605-2-1호'),
    array('region' => '해군', 'name' => '리더십센터', 'post' => 51638, 'address' => '경상남도 창원시 진해구 냉천로148번길 67 산 85번지', 'addressDetail' => ''),
    
    array('region' => '공군', 'name' => '장교교육대대', 'post' => 52634, 'address' => '경남 진주시 금산면 송백로 46 사서함', 'addressDetail' => '306-14호'),
    array('region' => '공군', 'name' => '부사관교육대대', 'post' => 52634, 'address' => '경남 진주시 금산면 송백로 46 사서함', 'addressDetail' => '306-15호'),
    array('region' => '공군', 'name' => '신병1훈련대대', 'post' => 52634, 'address' => '경남 진주시 금산면 송백로 46 사서함', 'addressDetail' => '306-16호'),
    array('region' => '공군', 'name' => '신병2훈련대대', 'post' => 52634, 'address' => '경남 진주시 금산면 송백로 46 사서함', 'addressDetail' => '306-17호'),
    array('region' => '공군', 'name' => '신병3훈련대대', 'post' => 52634, 'address' => '경남 진주시 금산면 송백로 46 사서함', 'addressDetail' => '306-39호'),
    array('region' => '공군', 'name' => '신병4훈련대대', 'post' => 52634, 'address' => '경남 진주시 금산면 송백로 46 사서함', 'addressDetail' => '306-40호'),
    array('region' => '공군', 'name' => '군수1학교', 'post' => 52634, 'address' => '경남 진주시 금산면 송백로 46 사서함', 'addressDetail' => '306-7호'),
    array('region' => '공군', 'name' => '군수2학교', 'post' => 52634, 'address' => '경남 진주시 금산면 송백로 46 사서함', 'addressDetail' => '306-34호'),
    array('region' => '공군', 'name' => '정보통신학교', 'post' => 52634, 'address' => '경남 진주시 금산면 송백로 46 사서함', 'addressDetail' => '306-11호'),
    array('region' => '공군', 'name' => '행정1학교', 'post' => 52634, 'address' => '경남 진주시 금산면 송백로 46 사서함', 'addressDetail' => '306-37호'),
    array('region' => '공군', 'name' => '방공포병학교', 'post' => 42250, 'address' => '대구광역시 수성구 이천동 사서함', 'addressDetail' => '134-3호'),
    
    array('region' => '해병대', 'name' => '장교교육대대대', 'post' => 37897, 'address' => '경북 포항시 남구 오천읍 충무로 70 사서함', 'addressDetail' => '209-133-1호'),
    array('region' => '해병대', 'name' => '부사관교육대대', 'post' => 37897, 'address' => '경북 포항시 남구 오천읍 충무로 70 사서함', 'addressDetail' => '209-133-2호'),
    array('region' => '해병대', 'name' => '제1신병교육대대', 'post' => 37897, 'address' => '경북 포항시 남구 오천읍 충무로 70 사서함', 'addressDetail' => '209-133-3호'),
    array('region' => '해병대', 'name' => '제2신병교육대대', 'post' => 37897, 'address' => '경북 포항시 남구 오천읍 충무로 70 사서함', 'addressDetail' => '209-133-4호'),
    array('region' => '해병대', 'name' => '제3신병교육대대', 'post' => 37897, 'address' => '경북 포항시 남구 오천읍 충무로 70 사서함', 'addressDetail' => '209-133-5호'),
    array('region' => '해병대', 'name' => '제5신병교육대대', 'post' => 37897, 'address' => '경북 포항시 남구 오천읍 충무로 70 사서함', 'addressDetail' => '209-133-6호')
);

/* 우편등기 */
$config['stamp'] = array(
    ['name' => '일반우편', 'price' => 1000, 'ment' => '발송후 3~5일(평일기준)'],
    ['name' => '준 등기우편(등기추적 가능)', 'price' => 2500, 'ment' => '발송후 3~5일(평일기준)'],
    ['name' => '등기우편(일반등기)', 'price' => 3150, 'ment' => '발송후 3~5일(평일기준)'],
    ['name' => '익일특급(빠른등기)', 'price' => 4200, 'ment' => '발송후 1~2일(평일기준)'],
);

/* 관리자 우편이름 */
$config['admStamp'] = array(
    99 => '전체',
    0 => '일반우편',
    1 => '준 등기우편',
    2 => '등기우편',
    3 => '익일특급'
);

/* 페이징 갯수 */
$config['pagingCnt'] = array(
    50 => '50개씩 보기',
    100 => '100개씩 보기',
    300 => '300개씩 보기',
    500 => '500개씩 보기'    
);

/* 출력 컬러 */
$config['color'] = array(
    'all' => '전체',
    'black' => '블랙',
    'kraft' => '크라프트',
    'purple' => '연보라',
    'grren' => '그린',
    'ivory' => '아이보리',    
    'yellow' => '옐로우',
    'blue' => '블루',
    'pink' => '핑크',
    'tema' => '테마',
    'sdoku' => '스도쿠',
    'difference' => '숨은그림찾기'    
);

/* 사진가격 */
$config['photoPrice'] = 500;

/* 게시판 종류 */
$config['noticeCate'] = array(
    'notice' => '공지',
    'qna' => '문의',
    'review' => '리뷰'
);
    
/* 나이스 페이 */
$config['merchantKey'] = "EYzu8jGGMfqaDEp76gSckuvnaHHu+bC4opsSN6lHv3b2lurNYkVXrZ7Z1AoqQnXI3eLuaUFyoRNC6FkrzVjceg=="; // 상점키
$config['MID'] = "nicepay00m"; // 상점아이디

/* IS PRIVATE */
$config['isMe'] = $_SERVER['REMOTE_ADDR'] == '61.79.73.71';

/* 앱 구분 (미사용) */
$config['isApp'] = true;

/* 결제 */
$config['clientId'] = 'R2_b675488f4ff44cba95de01808d9054ad';
$config['secretKey'] = '30b55653900e4f519e44a398879e3f6f';

/* 결제 타입 */
$config['payType'] = array(
    'card' => '카드결제',
    'deposit' => '무통장입금',
    'naverpayCard' => '네이버페이',
    'kakaopay' => '카카오페이',
    'bank' => '계좌이체',
    'point' => '포인트결제'
);

/* 무통장입금 정보 */
$config['donglDepositName'] = "황종민";
$config['donglDeposit'] = array(    
    '112-2078-6389-00' => '부산은행',
    '010-8507-0600' => '기업은행',
);

/* 봉투 필터 */
$config['envelope'] = array(
    'all' => '전체',
    'normal' => '기본',
    'big' => '대봉투'
);

/* 봉투 무게 */
$config['envelopeWeight'] = array(
    'all' => '전체',
    'asc' => '오름차순',
    'desc' => '내림차순'
);

/* 현금영수증 관련(1차 카테고리) */
$config['depositType'] = array(
    'earnings' => '소득공제',
    'expenditure' => '지출증빙'
);

/* 현금영수증 관련(2차 카테고리) */
$config['depositType2'] = array(
    'phone' => '휴대폰번호',
    'business' => '사업자번호',
    'card' => '현금영수증카드'
);

/* 편지 + 사진 수량 35개 이상이면 대봉투(갯수 지정) */
$config['maxBigCnt'] = 30;

?>
