function StartScan() {
  Quagga.init({
    inputStream: {
      constraints: {
        width: 430,
        height: 250,
      },
      name: "Live",
      type: "LiveStream",
      target: document.querySelector('#scan-content'), // Pasar el elemento del DOM
    },
    decoder: {
      readers: ["code_128_reader", "ean_reader"]
      //readers: ["code_128_reader", "ean_reader", "ean_8_reader", "code_39_reader", "code_39_vin_reader", "codabar_reader", "upc_reader", "upc_e_reader", "i2of5_reader"]
    }
  }, function(err) {
    if (err) {
      console.log(err);
      $('#labelCam').show();
      // alert("La camara requiere permisos para usarse");
      return;
    }
    //console.log("Iniciado OK");
    Quagga.start();
  });
}

function StopScan() {
  Quagga.stop();
}
document.addEventListener("DOMContentLoaded", () => {
  //const $resultados = document.querySelector("#resultScan");
  //StartScan();
  Quagga.onDetected((data) => {
    swal({
      text: 'Escaneo satisfactorio',
      icon: "success",
      timer: 2000,
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
    $('#blockbtnReScan').show();
    $('#blockCam').hide();
    $("#blockCheck").hide();
    $("#blockDN").show();
    $('#resultScan').val(data.codeResult.code);
    $('#resultScan2').val(data.codeResult.code);
    // sessionStorage.setItem('banPast', 'A');
    $('#resultScan').prop("disabled", true);
    $("#blockEvidence").show();
    StopScan();
    processDN();
    // $resultados.textContent = data.codeResult.code;
    // Imprimimos todo el data para que puedas depurar
    // console.log(data);
  });
  Quagga.onProcessed(function(result) {
    var drawingCtx = Quagga.canvas.ctx.overlay,
      drawingCanvas = Quagga.canvas.dom.overlay;
    if (result) {
      if (result.boxes) {
        drawingCtx.clearRect(0, 0, parseInt(drawingCanvas.getAttribute("width")), parseInt(drawingCanvas.getAttribute("height")));
        result.boxes.filter(function(box) {
          return box !== result.box;
        }).forEach(function(box) {
          Quagga.ImageDebug.drawPath(box, {
            x: 0,
            y: 1
          }, drawingCtx, {
            color: "green",
            lineWidth: 2
          });
        });
      }
      if (result.box) {
        Quagga.ImageDebug.drawPath(result.box, {
          x: 0,
          y: 1
        }, drawingCtx, {
          color: "#00F",
          lineWidth: 2
        });
      }
      if (result.codeResult && result.codeResult.code) {
        Quagga.ImageDebug.drawPath(result.line, {
          x: 'x',
          y: 'y'
        }, drawingCtx, {
          color: 'red',
          lineWidth: 3
        });
      }
    }
  });
});