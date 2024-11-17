<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowed_ips = ['127.0.0.1', '::1'];
    $use_auth = true;
    $client_ip = $_SERVER['REMOTE_ADDR'];

    if (!in_array($client_ip, $allowed_ips) and $use_auth == true) {
        echo "Access denied. Your IP ($client_ip) is not allowed.";
        exit;
    }

    if (isset($_POST['command'])) {
        $command = $_POST['command'];
        
        if (strtolower($command) === 'dir') {
            $command = 'cmd.exe /c dir';
        }

        $output = shell_exec('cd C:\\nginx && '. $command . ' 2>&1');
        echo "<pre>$output</pre>";
    } else {
        echo "Invalid request.";
    }
    exit;
}

$client_ip = $_SERVER['REMOTE_ADDR'];
$server_domain = $_SERVER['HTTP_HOST'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Terminal</title>
    <link href="https://fonts.googleapis.com/css?family=Inconsolata" rel="stylesheet">
    <style>
        ::selection {
            background: #FF5E99;
        }
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
        }
        body {
            font-size: 11pt;
            font-family: Inconsolata, monospace;
            color: white;
            background-color: black;
        }
        #container {
            padding: .1em 1.5em 1em 1em;
        }
        #container output {
            clear: both;
            width: 100%;
        }
        #container output pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .input-line {
            display: flex;
            align-items: center;
            clear: both;
        }
        .input-line > div:nth-child(2) {
            flex-grow: 1;
        }
        .prompt {
            white-space: nowrap;
            color: #96b38a;
            margin-right: 7px;
            user-select: none;
        }
        .line {
            color: #96b38a
        }
        .cmdline {
            outline: none;
            background-color: transparent;
            margin: 0;
            width: 100%;
            font: inherit;
            border: none;
            color: inherit;
        }
    </style>
</head>
<body>
    <div id="container">
        <output></output>
        <div id="input-line" class="input-line">
            <div class="prompt"></div><div><input class="cmdline" autofocus /></div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script>
        $(function () {
            const clientIp = "<?php echo $client_ip; ?>";
            const serverDomain = "<?php echo $server_domain; ?>";
            $('.prompt').html(`[${clientIp}@${serverDomain}] #`);

            const $cmdline = $('.cmdline');
            const $output = $('#container output');

            $cmdline.on('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const command = $cmdline.val().trim();
                    $cmdline.val('');

                    if (!command) return;
                    
                    if (command.toLowerCase() === 'cls') {
                        $output.empty();
                        return;
                    }

                    $output.append(`<div class="line">[${clientIp}@${serverDomain}] # ${command}</div>`);

                    $.ajax({
                        url: '',
                        method: 'POST',
                        data: { command: command },
                        success: function (response) {
                            $output.append(response);
                            $output.scrollTop($output.prop("scrollHeight"));
                        },
                        error: function () {
                            $output.append('<div class="error">Error: Could not process the command.</div>');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
