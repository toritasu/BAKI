$('head').append(
  '<style type="text/css">.container { display: none; } .fade, .loader { display: block }'
);

$(window).on('load', function(){
  $('.fade').delay(900).fadeOut(800);
  $('.loader').delay(600).fadeOut(300);
  $('.container').css("display", "block");
});

$(function(){
  // 1. キャラ選択画面
  $('input[name=fighter]:radio').change(function(){
    var radioval = $(this).val(),
        widthPx = $('.container').innerWidth(),
        shiftPx = radioval * widthPx;
        console.log(radioval);
        console.log(widthPx);
        console.log(shiftPx);


    // いずれかのキャラが選択されたらボタンを活性化する
    $('.js-view-initial').hide();
    $('.js-btn-prohibit').removeClass('btn-inactive')
                         .addClass('btn-active animated')
                         .val('肉宴開幕ッ!!')
                         .prop('disabled', false);
    // 選択したグラップラーに応じてキャラ詳細をスライド
    $('.js-view-character').css('transform', 'translateX(' + -shiftPx + 'px)');

    // ※旧技術（読み込みが遅いことが判明したので没）
    // Ajax通信で選択されたキャラの情報を取り出す
    // $.ajax({
    //   type: "POST",
    //   url: "ajax.php",
    //   datatype: 'json',
    //   data: { key : radioval }
    // }).done(function(data){
    //   console.log('ajax通信しました');
    //   console.log(data);
    //   $('.js-character-name').text(data.name);
    //   $('.js-character-nickname').text(data.nickname);
    //   $('.js-character-description').text(data.description);
    //   $('.js-character-face').prop('src', data.imgSrc);
    // });

  });

  // 2-1. HP/MPゲージ
  var hpRemain = $('#js-hp-remain').text(),
      hpMax = $('#js-hp-max').text(),
      mpRemain = $('#js-mp-remain').text(),
      enemyHpRemain = $('#js-hp-remain-enemy').text(),
      enemyHpMax = $('#js-hp-max-enemy').text(),
      $hp = $('.js-gauge-hp'),
      $mp = $('.js-gauge-mp'),
      $enemyHp = $('.js-gauge-hp-enemy');
  hpPer = parseInt(hpRemain / hpMax * 100);
  mpPer = parseInt(mpRemain / 100 * 100);
  enemyHpPer = parseInt(enemyHpRemain / enemyHpMax * 100);
  // widthの書き換え
  if(hpPer < 5) {
    $hp.css('width', '5%');
  } else {
    $hp.css('width', hpPer + '%');
  }
  if(mpPer < 5) {
    $mp.hide();
  } else {
    $mp.css('width', mpPer + '%');
  }
  $mp.css('width', mpPer + '%');
  $enemyHp.css('width', enemyHpPer + '%');

  // 2-2. HPゲージ色変更
  if(hpPer <= 25) {
    $hp.toggleClass('pinch');
    $('#js-hp-remain').css('color', 'red');
  }
  if(enemyHpPer <= 25) {
    $enemyHp.toggleClass('pinch');
    $('#js-hp-remain-enemy').css('color', 'red');
  }
  // 2-3. リミットブレイク
  if(mpPer === 100) {
    console.log(mpPer);
    console.log('リミットブレイク!!');
    $('.js-btn-lethal').show();
    $('.js-fighter-info-face').addClass('limit-break');
  }

  // 3. エフェクト
  preventEvent = true;
  // 3-1. 攻撃エフェクト
  $('input[name="attack"]').click(function(e){
    var $that = $(this), // setTimeout関数で使えるようにthisを変数に格納
        $attack = $('.effect-attack'),
        $enemy = $('.enemy-img');
    if(preventEvent){
      e.preventDefault();
      console.log('攻撃した！');
      $attack.addClass('attacked');
      $enemy.addClass('damaged');
      setTimeout(function(){
        preventEvent = false;
        $that.trigger('click');
      }, 1000);
    }
  });
  // 3-2. 必殺技エフェクト
  $('input[name="lethal"]').click(function(e){
    var $that = $(this), // setTimeout関数で使えるようにthisを変数に格納
        $cutin = $('.js-lethal-cutin');
    if(preventEvent){
      e.preventDefault();
      console.log('必殺技!!');
      $cutin.addClass('show');
      setTimeout(function(){
        preventEvent = false;
        $that.trigger('click');
      }, 2000);
    }
  });

  // 4. 自動スクロール
  var $historyWindow = $('.js-auto-scroll');
  $historyWindow.animate({scrollTop: $historyWindow[0].scrollHeight}, 'fast');
  height = $('.js-auto-scroll')[0].scrollHeight;
  console.log(height);

});
