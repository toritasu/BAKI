$(function(){
  $('input[name=fighter]:radio').change(function(){
    var radioval = $(this).val();

    $('.js-view-initial').hide();
    $('.js-btn-prohibit').removeClass('btn-inactive')
                         .addClass('btn-active animated')
                         .val('肉宴開幕ッ!!')
                         .prop('disabled', false);
    $.ajax({
      type: "POST",
      url: "ajax.php",
      datatype: 'json',
      data: { key : radioval }
    }).done(function(data){
      console.log('ajax通信しました');
      console.log(data);
      $('.js-character-name').text(data.name);
      $('.js-character-nickname').text(data.nickname);
      $('.js-character-description').text(data.description);
      $('.js-character-face').prop('src', data.imgSrc);

    });

  });
});
