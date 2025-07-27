var nextModeLink = document.getElementById('nextModeLink');


  // 選擇的模式
  var selectedMode = '';
  // 是否為練習模式
  var practiceMode = true;
  
  let xhr = new XMLHttpRequest();
  xhr.open("POST", "get-nextLink.php", true);
  xhr.onload = ()=>{
    if(xhr.readyState === XMLHttpRequest.DONE){
        if(xhr.status === 200){
          let data = xhr.response;
          if(nextModeLink){
            nextModeLink.href = data;
          }
        }
    }
  }
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.send('mode=' + selectedMode + "&practice=" + practiceMode );
