window.addEventListener('load',()=>{


  $(document).ready(function() {
    /*$('button.alm-load-more-btn.more').on('click', ()=>{
      setTimeout(massPreview, 5000)	
    })
    function massPreview(){
    
    var sendRequestEachValue = function (values, containers) {
      var newMetas = values,
          meta = newMetas[0]
      newMetas.splice(0,1)
    
      var newContainers = containers,
          container = newContainers[0]
      newContainers.splice(0,1)	

      var jqXHR = jQuery.post(
        myPlugin.ajaxurl,
        {
            action: 'meta_preview',
            link:meta
        }
      );
      
      
      jqXHR.done(function (response) {
        $('#loading').text('loading');
        $(container).show();
        $(container).html(response);
      });
      
      jqXHR.fail(function () {
        $(container).html('<img src="https://beesmartstg.wpengine.com/wp-content/uploads/2022/02/not-fond.jpg" class="container-resposne">');

      });

     
    }
    var metas = document.querySelectorAll('.meta-preview');
    var containerResposnes = document.querySelectorAll('.container-resposne');
    
    
    var metasArray= [] 
    var containerResponsesArray = []
    
    containerResposnes.forEach(container =>{
      containerResponsesArray.push(container)
    })
    
    metas.forEach(meta =>{
      metasArray.push(meta.value)
    })
    
    
    metas.forEach(m =>{
      sendRequestEachValue(metasArray,containerResponsesArray)
    })
  }
  setTimeout(massPreview, 5000)*/
  });
})