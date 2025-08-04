const translationDataElement = document.getElementById("translation-data");
const timerText = translationDataElement.dataset.timerText;
const minuteText = translationDataElement.dataset.minuteText;
const secondText = translationDataElement.dataset.secondText;
const expiredText = translationDataElement.dataset.expiredText;

const timeDataElement = document.getElementById("time-data");
const seconds = timeDataElement.dataset.secondsText;
const minutes = timeDataElement.dataset.minutesText;

function countdown(element, minutes, seconds) {
    var time = minutes * 60 + seconds;
    var interval = setInterval(function() {
        var el = document.getElementById(element);
        if (time <= 0) {
            el.innerHTML = expiredText;
            clearInterval(interval);
            return;
        }
        var minutes = Math.floor(time / 60);
        if (minutes < 10) minutes = "0" + minutes;

        var seconds = time % 60;
        if (seconds < 10) seconds = "0" + seconds;

        var text = timerText + ' ' + minutes + ":" + seconds + '!';
        el.innerHTML = text;
        time--;
    }, 1000);
}

countdown("timer", Number(minutes), Number(seconds));