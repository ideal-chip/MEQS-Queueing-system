<?php
$filesPath = "../files/";

$query = "SELECT * FROM audios";
$audioList = getArrayAssoc($query);

$shortBeep = getSetting('audioShortBeep');
//var_dump($audioList);
?>
<audio id="ad" style="display:none;" onended='playNext();' preload="auto" ></audio>
<div style="text-align: center;">
    <div id="arr" style="color: red;">

    </div>
</div>


<script type="text/javascript">
    var playList = [];
    var playPointer = 0;
    var _audioElement = document.getElementById('ad');
    var arrElement;
    var counto = 0;
    var shortBeep = '<?php echo $shortBeep ?>';
    var path_audio_notification = '<?php echo $filesPath ?>att.mp3';

<?php echo "var audios = " . json_encode($audioList) . ";\n"; ?>


    //displayTicketAudio("", "", "", "");

    function pad(num, size) {
        var s = num + "";
        while (s.length < size)
            s = "0" + s;
        return s;
    }

    setInterval(function () {
        checkAudios();
    }, 1200);

    function getAudioIndex(id) {
        var index = 0;
        for (var i = 0; i < audios.length; i++) {
            if (audios[i].audio_id == id) {
                index = i;
                break;
            }
        }

        return index;
    }

    var checkAudioXML = new XMLHttpRequest();
    checkAudioXML.onreadystatechange = function () {
        if (checkAudioXML.status == 200 && checkAudioXML.readyState == 4)
        {
            var retJSON = JSON.parse(checkAudioXML.responseText);
            if (retJSON && retJSON.length)
            {
                for (var i = 0; i < retJSON.length; i++) {
                    char = retJSON[i].eventChar;
                    eventNo = retJSON[i].eventNo;
                    counter = retJSON[i].Counter;
                    audioID = retJSON[i].audioID;

                    id = getAudioIndex(audioID);
                    path = audios[id].audio_path;
                    lang = audios[id].audio_language;
                    gender = audios[id].audio_gender;

                    var curPath = '';
//                    console.log("read "+ shortBeep);
                    if (shortBeep == 'active') {
                        notification();
                        curPath = path_audio_notification;
                    } else {
                        if (gender == 1) {
                            preparePlaylistFemale(path, lang, char, eventNo, counter);
                        } else {
                            preparePlaylistMale(path, lang, char, eventNo, counter);
                        }
                        curPath = path;
                    }


                    displayTicketAudio(char, eventNo, counter, curPath);
                }
            }
        }
    }

    function checkAudios() {
        checkAudioXML.open("GET", "../api/audio/index.php", true);
        checkAudioXML.send();
    }

    //==============================================================  | Audio notification
    function notification() {
//        var audio = new Audio(path_audio_notification);
//        audio.play();
        playList.push(path_audio_notification);
        if (!playPointer)
            playNext();
    }

    function preparePlaylistFemale(audioPath, language, service, number, counter) {
        var d1, d2;
        var ext = "ogg";
        d2 = (Math.floor(number / 100)) * 100;
        d1 = number % 100;
//        _audioElement = document.getElementById('ad');
        //playList.push(audioPath + "/" + "att." + ext);
        playList.push(audioPath + "/" + language + "/num." + ext);
        if (service.length == 1)
        {
            playList.push(audioPath + "/" + "characters/" + service.toLowerCase() + "." + ext);
        } else
        {
            srv = service.toLowerCase();
            for (var c = 0; c < srv.length; c++)
            {
                playList.push(audioPath + "/" + "characters/" + srv.substr(c, c + 1) + "." + ext);
            }
        }
        if (d2)
        {
            playList.push(audioPath + "/" + language + "/" + d2 + "." + ext);
            if (d1 != 0)
                playList.push(audioPath + "/" + language + "/and." + ext);
        }
        if (d1 != 0)
        {
            playList.push(audioPath + "/" + language + "/" + pad(d1, 3) + "." + ext);
        }
        playList.push(audioPath + "/" + language + "/cnt." + ext);
        playList.push(audioPath + "/" + language + "/" + pad(counter, 3) + "." + ext);
        if (!playPointer)
            playNext();
    }

    function preparePlaylistMale(audioPath, language, service, number, counter) {

        var client_befor = "CLIENT_";
        var client_after = "_ARA.";
        var counter_befor = "COUNTER_";
        var counter_after = "_ARA.";
        var ext = "WAV";



        number = number % 1000;
        //alert(number);
        playList.push(audioPath + "/" + client_befor + number + client_after + ext);
        playList.push(audioPath + "/" + counter_befor + counter + counter_after + ext);

        if (!playPointer) {
            playNext();

        }
    }

    function displayTicketAudio(char, eventNo, counter, path) {
        counto++;
        arrElement = document.getElementById('arr');
        arrElement.innerHTML = "count: " + counto + "   |   ticket: " + char + eventNo + "   |    counter: " + counter + "   |    path: " + path;
    }

    function playNext() {  //Added on onended events for audio element
        if (playPointer < playList.length)
        {
            _audioElement.src = playList[playPointer];
            playPointer += 1;
            _audioElement.load();
            _audioElement.play();

        } else
        {
            playList = [];
            playPointer = 0;
        }
    }

    setInterval(function () {
        checkRefresh();
    }, 2000);

    function checkRefresh() {
//        console.log("refresh");
        $.ajax({
            type: 'get',
            dataType: 'json',
            cache: false,
            url: '../api/checkupdate.php',
            data: {id: 1, type: 'audio'},
            success: function (response, textStatus, jqXHR) {

//                console.log("updated: " + response);
                if (response) {
                    shortBeep = response;
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
            }
        });
    }
</script>
