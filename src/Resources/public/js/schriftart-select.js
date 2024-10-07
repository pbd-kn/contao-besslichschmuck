
jQuery(document).ready(function(){

document.addEventListener('keydown', function(event) {
    console.log(`Key pressed: ${event.key}, Code pressed: ${event.code}`);

    // Überprüfen, ob die Ctrl- und Alt-Tasten gedrückt sind
    if (event.ctrlKey && event.altKey) {
        switch(event.code) {
            case 'KeyA':
                document.body.style.fontFamily = "'Arial', sans-serif"               
                document.body.style.setProperty('font-size', '18px', 'important');
                console.log('Ctrl + Alt + 1 pressed');
                break;
            case 'KeyB':
                document.body.style.fontFamily = "'Brush Script MT', cursive";
                document.body.style.setProperty('font-size', '21px', 'important');
                console.log('Ctrl + Alt + 2 pressed');
                break;
            case 'KeyC':
                document.body.style.fontFamily = "'Lucida Handwriting', cursive";
                document.body.style.setProperty('font-size', '18px', 'important');
                console.log('Ctrl + Alt + 3 pressed');
                break;
            case 'KeyD':
                document.body.style.fontFamily = "'Segoe Script', cursive";
                document.body.style.setProperty('font-size', '18px', 'important');
                console.log('Ctrl + Alt + 4 pressed');
                break;
            case 'KeyS':
                document.body.style.fontFamily = "'Comic Sans MS', cursive";
                document.body.style.setProperty('font-size', '18px', 'important');
                console.log('Ctrl + Alt + 5 pressed');
                break;
            case 'KeyF':
                document.body.style.fontFamily = "'Segoe Print', cursive";
                document.body.style.setProperty('font-size', '18px', 'important');
                console.log('Ctrl + Alt + 6 pressed');
                break;
            case 'KeyG':
                document.body.style.fontFamily = "'Mistral', cursive";
                document.body.style.setProperty('font-size', '18px', 'important');
                console.log('Ctrl + Alt + 7 pressed');
                break;
            case 'KeyH':
                document.body.style.fontFamily = "'Giddyup Std', cursive";
                document.body.style.setProperty('font-size', '18px', 'important');
                console.log('Ctrl + Alt + 8 pressed');
                break;
            case 'KeyI':
                document.body.style.fontFamily = "'Bradley Hand ITC', cursive";
                document.body.style.setProperty('font-size', '18px', 'important');
                console.log('Ctrl + Alt + 9 pressed');
                break;
            case 'KeyJ':
                document.body.style.fontFamily = "'Algerian', cursive";
                document.body.style.setProperty('font-size', '18px', 'important');
                console.log('Ctrl + Alt + A pressed');
                break;
            case 'KeyK':
                document.body.style.fontFamily = "'Kristen ITC', cursive";
                document.body.style.setProperty('font-size', '18px', 'important');
                console.log('Ctrl + Alt + B pressed');
                break;
            case 'KeyL':
                document.body.style.fontFamily = "'Caveat', cursive";
                document.body.style.setProperty('font-size', '18px', 'important');
                console.log('Ctrl + Alt + C pressed');
                break;
            case 'KeyM':
                document.body.style.fontFamily = "'Papyrus', cursive";
                document.body.style.setProperty('font-size', '18px', 'important');
                console.log('Ctrl + Alt + D pressed');
                break;
            case 'KeyN':
                document.body.style.fontFamily = "'Snell Roundhand', cursive";
                document.body.style.setProperty('font-size', '18px', 'important');
                console.log('Ctrl + Alt + N pressed');
                break;
            case 'KeyO':
                document.body.style.fontFamily = "'Vivaldi', cursive";
                document.body.style.setProperty('font-size', '18px', 'important');
                console.log('Ctrl + Alt + O pressed');
                break;
            case 'KeyP':
                document.body.style.fontFamily = "'Dancing Script', cursive";
                document.body.style.setProperty('font-size', '18px', 'important');
                console.log('Ctrl + Alt + P pressed');
                break;
            case 'KeyQ':
                document.body.style.fontFamily = "'Cursive', cursive";
                document.body.style.setProperty('font-size', '18px', 'important');
                console.log('Ctrl + Alt + Q pressed');
                break;
            case 'KeyR':
                document.body.style.fontFamily = "'Georgia', serif";
                document.body.style.setProperty('font-size', '18px', 'important');
                console.log('Ctrl + Alt + R pressed');
                break;
            default:
                break;
        }
    }
});
});
