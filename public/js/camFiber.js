  //FUNCION DEPRECADA
  //Tomar foto
  const widthRes = 800;
  const heightRes = widthRes * 0.80;
  const $SelectDevice = document.querySelector("#video-source");
  const sound = true;
  const soundsource = ['snap' => 'data:audio/mp3;base64,//NIZAAGVCkyCwwjEwdYBegAAAAARZJcAcEFEBgV3dzoinxACBQIg8cBAeD4P+GJz9khD6w8c5/4gOfxB/9/xAc9byhzf/i3yn+o5UclIY/+Ulz/+XkOf///KBj+Cf6jifB8TieOyNJRyOW2gCgTT+oy8EOQZy/N6+YujbbrMJkyf30lbni6ZiiEdu7WLSQ5//NIZCsJ8I1VL6SYAQfoAimXQBAC7PHz9v/pyIqsuTXzjsebtf+iQAA0MKBD2vFXtbUxqFvPonv+3uu6qqSVC7R9yJ+lcU3RXu9Ps6P+ynT/+pv/+z/p/ZaqSz8L4890050AemSmiEBufkmKZeH3BTGCqJJGJKIAKhP5IhaTHcFapREaSY/hNMNkgbTIzk6l//NIZDYQoZFOysEkAAV4BhgBgAAASoCp3frbRKpCep2wvARXKrr//YU9A+obgrPLlGdWp77/z//+f6l0yTrnDCN+Tnb1a6akJYeuefc/vf7/XbRrt5PUCiBoUR8qhv81HbutoXI+u7f3r/Z/f/6Oj9f/2I/7vX6fszHnr///QiJxjhJEV2C7Iz3OF4qp/NNR//NIZBYJ3XNKAMCoAIjIAhQBgAAAkLIcdOEV80nFxh5c416FHVFX6oPDbGkpim6VNJf6Gtcu6PacjH632RPr+Urdzo85xNO65GPibYrg7YQmvfYv+z2BJvv2KpuXqqtI0O0I+j8Wqe3br1J20q7HBqmRwSwACWyCy222SG1gAUGQEQ0fkVXL/+7P6tOt9v83//NIZB8MzX9fL8KcAQfwAhQBgAAAxR9688enEMxJxbqtLTJrLUqcp/1ZL6EGFk+qMbdRopkxhqeVHmiSfPIKTLATAPGhk8bXKD7ozmnzzIvRFqRdUfIRLJCON8Mmd25/rVdtoctVXLJt0q0s/9Xf0P3r+j9snolNu7sy9KqSC23C7a7bYbbSQdATjrbGyqS+//NIZBMK3TtnLsDAAQhgBhQBgQAAtSzFVlprTWl6lPUdZJkkzBtTIqQ1PR/XWm1TopqZD2bUmivZz/+vq616kl4DgSmxql+ocokv9ahumzZiEgVd7/0ggDhf36My6u1rtbrjbq6fsKq9X1Z6xPVqcvYnvdTRErV1/q11klFoFttmAAAA1rbsYAo6EWuJ1/bf//NIZBULNXtpL8E0AYowAhABgAAATNO3frZbf///////9G70y4afrTX+syb7JhbR6smrmZmskyeS6VBajp4yJEZIjQDiJqBqssI7pudUxt83N51J03b+bIgW1S9FSdfevaqlXRooepgCQyy97tFktussQK2qxdlRn7A+1MporIJ0PSqS223fb/MIC1ZhQ30X//NIZA0KeSVbLuM0AQiAAgQBwBAARbS7XrNf/wymJJ0E01rMDZE8pNGxoszrSWpIxWkbJonjFNFS0qRxL+pSUxd1XW3bZKtFL/+vf///oLUxCAmKRs60ZnnszLPyYx7l0Rz5BdPR06OzqFJTfvXronOGv+XuS4VU1mX/sZ+6bttu2t29IgD36ni54dh4Twt///NIZBIIaSFZLyAmuQe4Bg4GCEQANtwaHwOjLGSlRlGzMXfcTV06VReq/+RAUvKcGzADDVMzlH//+X///8LgxF3/ivJSR2khCgAgEDb7nr2oT1d/sb+5Pv7Wt/6a//33f3f//oRVCnTcct9AgDCjoUMkdhFyyqHDOqvmrl90Ijv5XRGEg8i66/KyTfol0zIj//NIZCoJBSU83xRq0AfYBh42CAAA2oCWbWDQr31rozIcZ///S5MWcDJQfFj//qej6aFUOuuVVVaSwTrFQ1kNXZt7fv1fV+5fjN1n3ft/r//9PoUm7/442GCruB61EXJyd7iijJt57Tu/P4LrlLprHE6+pBL52yfv9/Q4oqUKiLMXb61O9WQAYlb2Vy7////u//NIZD0I2SFEzhTCPwiABh2WAAACiAN//tX/SkVcPZpmVViTQ0fZJisxRl/Yr3+6BnU//u34707Pvv9///9O1KUr5Ry26YsIZOZAFOhWUD6IhM89+nBBDTPP6R2YVO2EJmNXZHpRk6fberOjf6cdosZiwsbMaometP//+yhYIMIKTP9UInwxt9KKe1BZvfcp//NIZE8JESk83iRlPAegAggAAAAAdX0I/9eYrRo3X96ezvbsardXT/j/mLHICqqqiAxJM4VdOm5/9LisZZFybnrkynbbU+HFhwNyaTnBDHe86CAotYAWx6nI/Dj1P/o0t844TxpyQ/LvxwAE58TvaKtS4L793+S3L1fUi36qq/r612//7n0fR746t3p3qpbb//NIZGMI1L8+eaGIAAZgAggBQAAABaLbFWLTYaxUGyAJMIQo9GEP/H/0gybWbRFKJDtGhvXxiCwaBfO4wv9LxDtwFxAB+6IhrT/8aRNDxQPHSFS7jr/fujR6DFg6qVT0/r//frp6m4lKPRRivmSW3x/zd13HX9uwqLB8NIoeaKlUlpeUJWeLt3f6Xd6O/tdp//NIZH0OPXNhL8GgAAb4AhQBgwAAaXq2euM937P1XlttbP/+L6bq7asDEeIbjro0NI+7hZh88OxA1rnp90fg1JRumPP8es9eqkoadOGivzt+nxPeu4wsphmw6dccagdqT8rj+oSGJybAVHckOtUaX0bFjyw4qvfJIOTcHknL2w+QAJ7CM4sUescP3zzEW35t//NIZGoPgZFiAMEsAAx4AjJfgRAAnuZdXV6bql7NVz5caVl7IM1btbYbFgYeYiVlkbbjgAUeUt9y9wzdyajgkZZ69itzjwt3MuH+rVXuu1dX62bhtX0+61V2ZYhFaXOabOVypJOFsAPS/YgfS/ALoGCQQ4ZWF7NLpeyDiKH8nOJUmjkW1B04VlqhouT5VMxt//NIZDcQyYeFf8esAYTYAhgBgAAAFEB682JpPISVlU/bsdbbLaHeP8Zsds5Hu6iJN0B2LFZ87VItSO079rf277fe+K676/b+ojC3WpPc0pKEIGlHP+P/+P49nddfzf//7GHGQe//2f6+vZ/6v6dv/9L/d/6EJ/9K4+///301tttljkkYAA0/GQQEo0tRM2T2//NIZBgMpP+TL8QgAQgoAhQBgAAAHwdnTZlUUGQbF301ShTnmSCwXJoYLCDFpeeYKA6H2zC0kB18TEYsKiq5s1fX+v+ow0piiix//XbfPH7FToRJxYTc0WNltIZLp+wNnQ1VT4v1SCcqr/c5m1bN99ib/frVuyt6jmuk1/5B/atn9iK7lZiXZDkRBSw1SdYT//NIZA0JjNV5j+MMAAfwAhFnwAAAQwvO446GZFcY+t5gOeZ3T4iU4nnXLif73Kacy56fwtzr/n+hcSvEiE8ABBMTvU69Z+72Vg+J1GSjP/0Djn7CEtbyDAmqaopfRqovbfG9r/Rs/9Pq/0/N//v7W+hG9ndV2u2sjfwAmiMYqqnqWLGCJEMkhMQs70WJVTkQ//NIZBsLfSdzKyTCXQdYAggAAAAAXcOohoLM4/dn+JRZbloNq/MwVZetWOeRUwZ9sMGHU1vzOrIYsMAoBOUuXqVjOr+xW1/5tPKFOvuA/6jtFc9V6COn3rXpV76tdivT+7Xben9FqNH2f9VhavJs/Zax+pUHiIiIVVkibTgu5WLOjE0lTMSeCFTomigEgEAp//NIZBwJ4NN7jwBiDwhoBimWCAACL0ldDOFECvQz9eUSVrFRwISop6t2/9TXVgrFhUoAg+Ji7AYNxyI8qK39Ut/uY9B7y1AHr9q+qklQmDbzc8/cjtehrnLf5T3fa//fR5H+n//Gf/+nUhdsN97XGknR8EgSyfPs1h8PwES0qx/NKJYGAlJK1P/M9ynZRnKg//NIZCYIEJ1rLwRiZQjIBh5WAAAAsS+pxiGhda2OhuV/b+yQCT/iSGyrhaJZL2Ek1HFI3exSoap7T89CuzdSlhGj7EVtdT5FHZ+r//1dP/93/0VVVXuXd42iCTwKup0ip7TwiCgKgqdKu4dEpZ4a4lDRYGjxEsJQkPd8ktxZig6SLDyx6GsFSz/LP+WEQNeV//NIZD0IxANLjwQiAAcoAggAAAAAIliIKw7xEeT0/++rs1WbBFtVb2dKft2X35VivR//upv2I329T6GFNJN5uyxNlcIsVcqLu06n/U/bTyK/v/5H//5FH6//+R/TJLLIG43G44CfcVeGpbOiX8kW1uzydbMlX8ed7Vf6f8798S6jzmszqP/yCkxBTUUzLjk5//NIZFUEPAMHfwAiAAqgBh5eCAACLjWqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq', 'trash' => 'data:audio/mp3;base64,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', 'save' => 'data:audio/mp3;base64,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'];

  function alerta(mensaje, time) {
    swal({
      text: mensaje,
      icon: "warning",
      timer: time,
      timerProgressBar: true,
      didOpen: () => {
        Swal.showLoading()
        const b = Swal.getHtmlContainer().querySelector('b')
        timerInterval = setInterval(() => {
          b.textContent = Swal.getTimerLeft()
        }, 100)
      },
      willClose: () => {
        clearInterval(timerInterval)
      },
      button: {
        text: "OK"
      }
    });
  }

  function supportMedia() {
    const permit = !!(navigator.getUserMedia || (navigator.mozGetUserMedia || navigator.mediaDevices.getUserMedia) || navigator.webkitGetUserMedia || navigator.msGetUserMedia);
    if (!permit) {
      alerta('Tú navegador no soporta el uso de la cámara, te recomendamos usar Chrome', 6000);
    }
    return permit;
  }

  function permitMedia(cam = "environment") {
    //frontal
    let type = 'user';
    if (getTypeCam() == 'movil' && cam == "environment") {
      //trasera
      let type = 'environment';
    }
    try {
      navigator.mediaDevices.getUserMedia({
        audio: false,
        video: {
          facingMode: {
            exact: type
          }
        }
      }).then(stream => {
        return stream;
      }).catch(e => {
        console.log(e.toString());
        alerta('Algo falló en obtener el dispositivo de captura de imagen, por favor intenta de nuevo..' + e.toString(), 6000);
        return false;
      });
    } catch (e) {
      alerta('Falló en obtener los permisos del dispositivo de captura de imagen. ' + e.toString(), 6000);
      return false;
    }
  }

  function getTypeCam() {
    if (navigator.userAgent.match(/Android/i) || navigator.userAgent.match(/webOS/i) || navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/iPad/i) || navigator.userAgent.match(/iPod/i) || navigator.userAgent.match(/BlackBerry/i) || navigator.userAgent.match(/Windows Phone/i)) {
      return 'movil';
    }
    return 'desktop';
  }
  let initCam = (videoTag) => {
    let failCam = true;
    if (supportMedia()) {
      permitMedia();
      navigator.mediaDevices.enumerateDevices().then(devices => {
        let deviceBack = false;
        let deviceFront = false;
        console.log('devices ', devices);
        let navegador = navigator.userAgent;
        devices.forEach(device => {
          if (device.kind == "videoinput") {
            if (getTypeCam() == 'movil' && device.label.length == 0) {
              deviceBack = device;
              console.log('idMovil ', device.deviceId);
            } else {
              if (device.label && device.label.length > 0) {
                if (device.label.toLowerCase().indexOf('back') >= 0) {
                  deviceBack = device;
                } else {
                  deviceFront = device;
                }
              }
            }
          }
        });
        if (deviceBack || deviceFront) {
          navigator.mediaDevices.getUserMedia({
            video: {
              width: widthRes,
              height: heightRes,
              deviceId: deviceBack ? deviceBack.deviceId : deviceFront.deviceId
            }
          }).then(stream => {
            videoTag.height(videoTag.width() * 0.80);
            videoTag[0].srcObject = stream;
            videoTag[0].play();
            failCam = false;
          }).catch(e => {
            console.log(e.toString());
            alerta('Algo falló en obtener el dispositivo de captura de imagen, por favor intenta de nuevo..', 6000);
          });
        } else {
          alerta('No se pudo configurar la camara a utilizar. Posiblemente debes dar permisos en tu dispositivo', 6000);
        }
      }).catch(e => {
        console.log(e.toString());
        alerta('Algo falló en obtener el dispositivo de captura de imagen, por favor intenta de nuevo.', 6000);
      });
    }
    if (failCam) {
      //$('#block_videocam video').attr('hidden', true);
    }
  }
  /*$('#modalChangeStatus').on('hide.bs.modal', function(event) {
    if(videoTag && videoTag.length && videoTag[0].srcObject){
      videoTag[0].srcObject.getTracks()[0].stop();
    }
    //detengo scam
    StopScan();
  });*/
  let resetPicFlow = (videoTag, current) => {
    $('#img-' + current).attr('hidden', true);
    $('#btnDesPic-' + current).attr('hidden', true);
    $('#btnTakePic-' + current).attr('hidden', null);
    videoTag.attr('hidden', null);
  }
  let dataURLtoBlob = (dataURL) => {
    let arr = dataURL.split(','),
      mime = arr[0].match(/:(.*?);/)[1],
      bstr = atob(arr[1]),
      n = bstr.length,
      u8arr = new Uint8Array(n);
    while (n--) {
      u8arr[n] = bstr.charCodeAt(n);
    }
    return new Blob([u8arr], {
      type: mime
    });
  }
  //Fin de tomar foto
  $(function() {});

  function changerView(element, ClassAdd, Classdescart) {
    if ($(element).hasClass(Classdescart)) {
      $(element).removeClass(Classdescart);
      $(element).addClass(ClassAdd);
    }
  }

  function btnTakePic(current, next) {
    //Captura
    const canvasTag = $('#canvas-' + current);
    const videoTag = $('#video-' + current);
    canvasTag.attr('width', widthRes);
    canvasTag.attr('height', heightRes);
    let canvasContext = canvasTag[0].getContext('2d');
    canvasContext.drawImage(videoTag[0], 0, 0, widthRes, heightRes);
    videoTag.attr('hidden', true);
    $('#img-' + current).attr('src', canvasTag[0].toDataURL('image/png'));
    $('#img-' + current).attr('hidden', null);
    $('#btnDesPic-' + current).attr('hidden', null);
    $('#btnTakePic-' + current).attr('hidden', true);
    videoTag[0].srcObject.getTracks()[0].stop();
    if (sessionStorage.getItem('photo-' + current) == 'null' || sessionStorage.getItem('photo-' + current) == 'false') {
      sessionStorage.setItem('photo-' + current, true);
    }
    switch (current) {
      case "recibo":
        createCam(next, 'identiP');
        $('#error-photo-recibo').text('');
        $('#block_document').attr('hidden', null);
        // runElement('#block_phot_identiF', false, -(heightRes + heightRes / 2));
        break;
      case "identiF":
        createCam(next, 'end');
        $('#error-photo-identiF').text('');
        $('#block_phot_identiP').attr('hidden', null);
        changerView('#block_phot_identiF', 'col-md-6', 'col-md-8');
        break;
      case "identiP":
        //Se pide generar el QR
        $('#error-photo-identiP').text('');
        $('#block_number_identi').attr('hidden', null);
        $('#block_tyc').attr('hidden', null);
        $('#btnqr_tyc').attr('hidden', null);
        changerView('#block_phot_identiP', 'col-md-6', 'col-md-8');
        break;
      default:
        // statements_def
        break;
    }
  }

  function btnDesPic(current) {
    //Eliminacion y nueva photo
    const videoTag = $('#video-' + current);
    resetPicFlow(videoTag, current);
    initCam(videoTag);
    sessionStorage.setItem('photo-' + current, false);
    if (current != 'recibo') {
      changerView('#block_phot_' + current, 'col-md-8', 'col-md-6');
    }
  }

  function createCam(current, next) {
    // $("#video-content").attr("id", "video-" + current);
    const videoTag = $('#video-' + current);
    initCam(videoTag);
    if (current == "recibo") {
      $('#block_recibo').attr('hidden', null);
    }
  }