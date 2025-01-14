window.uploadingAnimation = undefined;
window.stopAnimation = undefined;

$(document).ready(function(){
	var windowWidth = $(window).width();
	var footerHeight = $('.footer').height();
	/* 컨텐츠 영역 최소 높이 */
	$('.wrap').css('min-height', $(window).height()-footerHeight);
	//창 가로 크기가 575 미만일 경우(mobile)
	if(windowWidth < 575) {
		$('.wrap').css('min-height', $(window).height()-footerHeight);
		$('.history_area .btn').removeClass('on').next('.list').css('display','none'); //모바일버전 히스토리 접힘상태 디폴트
	}

	/* 창크기 조절시 컨텐츠 영역 최소 높이 */
	$(window).on('resize', function() {
		var windowWidth = $(window).width();
		var footerHeight = $('.footer').height();
		$('.wrap').css('min-height', $(window).height()-footerHeight);
		//창 가로 크기가 575 미만일 경우(mobile)
		if(windowWidth < 575) {
			$('.wrap').css('min-height', $(window).height()-footerHeight);
		}
	});

	/* (모바일)검색시 화면 움직임 막음 */
	$('.link_search').click(function() {
		$('body').css('overflow','hidden');
	});
	$('.search_area a').click(function() {
		$('body').css('overflow','inherit');
	});

	/*
	$('.modal_box').click(function(event) {
		if (event.target.id === 'modal-S') {
		$('.modal_box:target').css('opacity','0');
		}
	});
	*/

	/* placeholder 적용 (for ie9) */
	$('input, textarea').placeholder();

	/* select box */
	$(document).find('select').show();
	$('select:not(.ignore)').niceSelect(); //style 제거시엔 select에 ignore 클래스 추가

	/* 맨위로 이동 버튼 */
	$('#moveTop').on('click',function(e){
		e.preventDefault();
		$('html, body').animate({scrollTop:0}, 400);
	});
	$(window).scroll(function() {
		if ($(document).scrollTop() > 100) {
			$('#moveTop').addClass('show');
		} else {
			$('#moveTop').removeClass('show');
		}
	});

	/* 히스토리 토글 */
	$('.history_area .btn').click(function() {
		$(this).toggleClass('on').next('.list').slideToggle(300);
	});

	/* 배포 박스 삭제 */
	$('.item .btn_del').click(function() {
		$(this).parent().parent('.item').addClass('box_type_del').find('.tit_box .txt').hide();
		$(this).parent().parent('.item').find('.tit_box').append('<span class="txt_del">삭제됨</span>');
	});
	$('.item .btn_re').click(function() {
		$(this).parent().parent('.item').removeClass('box_type_del').find('.tit_box .txt').show()
		$(this).parent().parent('.item').find('.txt_del').remove();
	});

	/* select 직접입력 */
	$('.search_area select').change(function(e) {
		var inpSelp = $('.search_area .inp_self');
		$('.search_area select option:selected').each(function() {
			if($(this).val() == 'self') { //직접입력일 경우
				inpSelp.val(''); //값 초기화
				inpSelp.attr('disabled',false).show(); //활성화
				setTimeout(function(){ //포커스 이동
					inpSelp.focus();
				}, 0);
			} else { //직접입력이 아닐경우
				inpSelp.val($(this).text()); //선택값 입력
				inpSelp.attr('disabled',true).hide(); //비활성화
		 	}
		});
	});

	/* 클릭시 on 클래스 제어 */
	var onChange = function(target) {
		$(target).click(function(){
			$(this).addClass('on').siblings().removeClass('on');
		});
	}
	onChange('.tab_version a');
	onChange('.tab_os a');

	/* loading : 220127추가 */
	window.uploadingAnimation = function( target ) {
		var data = eval( 'loading' );
		var ani = bodymovin.loadAnimation({
			wrapper: document.getElementById( target ),
			renderer: 'svg',
			loop: true,
			prerender: false,
			autoplay: true,
			animationData: data
		});
		ani.play();
		$('.loading_dimm').css('visibility', 'visible');
		$('.loading_dimm').parents('body').css('overflow','hidden');
		// console.log('111.ozlab:::target(' + target + ') =>', document.getElementById( target ));
	};
	window.stopAnimation = function() {
		$('.loading_dimm').parents('body').css('overflow','visible');
		$('.loading_dimm').css('visibility', 'hidden');
		// console.log('222.ozlab::: =>', document.getElementById('loading_dimm'));
	}
	// 사용법: Usage
	// Usage of turning ON
	// window.uploadingAnimation( 'loadingAni' );
	// Usage of turning OFF
	// window.stopAnimation();
});
