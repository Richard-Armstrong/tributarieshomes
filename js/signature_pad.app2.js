var wrapper = document.getElementById("signature-pad");
var clearButton = wrapper.querySelector("[data-action=clear]");
var undoButton = wrapper.querySelector("[data-action=undo]");
var saveButton = wrapper.querySelector("[data-action=save]");
var editSaveButton = wrapper.querySelector("[data-action=editSave]");
var canvas = wrapper.querySelector("canvas");
var signaturePad = new SignaturePad(canvas, {
  // It's Necessary to use an opaque color when saving image as JPEG;
  // this option can be omitted if only saving as PNG or SVG
  backgroundColor: 'rgb(255, 255, 255)'
});

function resizeCanvasToDisplaySize() {
   // look up the size the canvas is being displayed
   const width = canvas.clientWidth;
   const height = canvas.clientHeight;

   // If its resolution does not match change it
   if (canvas.width !== width || canvas.height !== height) {
     canvas.width = width;
     canvas.height = height;

	 signaturePad.clear();
     return true;
   }

   signaturePad.clear();
   return false;
}

// On mobile devices it might make more sense to listen to orientation change,
// rather than window resize events.
window.onresize = resizeCanvasToDisplaySize;

clearButton.addEventListener("click", function (event) {
  signaturePad.clear();
});

undoButton.addEventListener("click", function (event) {
  var data = signaturePad.toData();

  if (data) {
    data.pop(); // remove the last dot or line
    signaturePad.fromData(data);
  }
});

saveButton.addEventListener("click", function (event) {
  if (signaturePad.isEmpty()) {
    alert("Please provide a signature first.");
  } else {
	saveButton.setAttribute('disabled', 'disabled');
    var dataURL = signaturePad.toDataURL('image/png');
    try_submit(dataURL);
  }
});

editSaveButton.addEventListener("click", function (event) {
  if (signaturePad.isEmpty()) {
    alert("Please provide a signature first.");
  } else {
	editSaveButton.setAttribute('disabled', 'disabled');
    var dataURL = signaturePad.toDataURL('image/png');
    edit_entry(dataURL);
  }
});
