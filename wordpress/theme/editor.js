jQuery(function ($) {
  $('.rssa-select-photo').on('click', function () {
    const container = $(this).closest('.rssa-photo');
    const picker = wp.media({ title: 'Choose a website photo', library: { type: 'image' }, multiple: false });
    picker.on('select', function () {
      const photo = picker.state().get('selection').first().toJSON();
      container.find('input').val(photo.id);
      container.find('img').attr('src', photo.url);
    });
    picker.open();
  });
  $('.rssa-reset-photo').on('click', function () {
    const container = $(this).closest('.rssa-photo');
    container.find('input').val(0);
    container.find('img').attr('src', $(this).data('default'));
  });
});

jQuery(function ($) {
  const list = $('#rssa-gallery-list');
  let photos = JSON.parse(document.getElementById('rssa-gallery-data').textContent);
  function render() {
    list.empty();
    $('#rssa-gallery-empty').toggle(!photos.length);
    photos.forEach((photo, index) => {
      const row = $('<div>').css({borderBottom:'1px solid #ddd',padding:'20px 0'}).appendTo(list);
      $('<img>').attr({src:photo.url,alt:''}).css({width:'160px',height:'120px',objectFit:'cover',display:'block',marginBottom:'12px'}).appendTo(row);
      $('<input>').attr({type:'hidden',name:'gallery['+index+'][id]'}).val(photo.id).appendTo(row);
      [['caption','Caption / event name'],['alt','Photo description (alt text)']].forEach(([key,label]) => {
        const field = $('<label>').text('Photo '+(index+1)+' — '+label).css({display:'block',marginBottom:'12px'}).appendTo(row);
        $('<input>').attr({type:'text',name:'gallery['+index+']['+key+']',class:'large-text'}).val(photo[key] || '').on('input',function(){photo[key]=this.value;}).appendTo(field);
      });
      [['Move up',-1],['Move down',1],['Remove',0]].forEach(([label,direction]) => {
        $('<button>').attr({type:'button',class:'button','aria-label':label+' photo '+(index+1)}).text(label).css('marginRight','8px').prop('disabled',direction !== 0 && (index+direction<0 || index+direction>=photos.length)).on('click',function(){
          if (!direction) photos.splice(index,1);
          else [photos[index],photos[index+direction]]=[photos[index+direction],photos[index]];
          render(); $('#rssa-add-gallery').trigger('focus');
        }).appendTo(row);
      });
    });
  }
  $('#rssa-add-gallery').on('click',function(){
    const picker=wp.media({title:'Add event photos',library:{type:'image'},multiple:true,button:{text:'Add to gallery'}});
    picker.on('select',function(){
      picker.state().get('selection').toJSON().forEach(photo=>{
        if (!photos.some(item=>item.id===photo.id)) photos.push({id:photo.id,url:photo.url,caption:'',alt:photo.alt || ''});
      });
      render();
    });
    picker.open();
  });
  render();
});
