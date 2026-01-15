<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dino Chrome ES5 Edition</title>
    <style>
        * { -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; }
        body { 
            margin: 0; padding: 0; overflow: hidden; 
            background-color: #f7f7f7; color: #535353;
            font-family: "Courier New", Courier, monospace;
            display: flex; justify-content: center; align-items: center; height: 100vh;
        }
        #game-container {
            width: 100%; max-width: 600px;
            min-width: 320px; position: relative;
            background: #fff; border: 1px solid #ddd;
        }
        canvas { display: block; width: 100%; height: auto; image-rendering: pixelated; }
        .ui { position: absolute; top: 10px; right: 10px; font-weight: bold; font-size: 18px; }
        #game-over {
            display: none; position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%); -webkit-transform: translate(-50%, -50%);
            text-align: center;
        }
    </style>
</head>
<body>

<div id="game-container">
    <div class="ui">HI <span id="high-score">00000</span> <span id="score">00000</span></div>
    <div id="game-over">
        <h1>G A M E  O V E R</h1>
        <p>Chạm hoặc nhấn Space để chơi lại</p>
    </div>
    <canvas id="dinoCanvas"></canvas>
</div>

<script>
(function() {
    var canvas = document.getElementById('dinoCanvas');
    var ctx = canvas.getContext('2d');
    var scoreDisplay = document.getElementById('score');
    var highDisplay = document.getElementById('high-score');
    var gameOverScreen = document.getElementById('game-over');

    var GAME_WIDTH = 600;
    var GAME_HEIGHT = 150;
    canvas.width = GAME_WIDTH;
    canvas.height = GAME_HEIGHT;

    var gravity = 0.6;
    var gameSpeed = 5;
    var score = 0;
    var highScore = 0;
    var isPlaying = true;
    var obstacles = [];

    function Dino() {
        this.width = 40;
        this.height = 40;
        this.x = 20;
        this.y = GAME_HEIGHT - this.height;
        this.vy = 0;
        this.jumpForce = -12;
        this.grounded = true;
    }

    Dino.prototype.draw = function() {
        ctx.fillStyle = "#535353";
        // Vẽ thân Dino (đơn giản hóa bằng các khối)
        ctx.fillRect(this.x, this.y, this.width, this.height);
        ctx.fillStyle = "#fff";
        ctx.fillRect(this.x + 25, this.y + 5, 5, 5); // Mắt
    };

    Dino.prototype.update = function() {
        if (this.y < GAME_HEIGHT - this.height) {
            this.vy += gravity;
            this.grounded = false;
        } else {
            this.vy = 0;
            this.grounded = true;
            this.y = GAME_HEIGHT - this.height;
        }
        this.y += this.vy;
    };

    Dino.prototype.jump = function() {
        if (this.grounded) {
            this.vy = this.jumpForce;
        }
    };

    function Cactus() {
        this.width = 15 + Math.random() * 20;
        this.height = 20 + Math.random() * 30;
        this.x = GAME_WIDTH;
        this.y = GAME_HEIGHT - this.height;
    }

    Cactus.prototype.draw = function() {
        ctx.fillStyle = "#535353";
        ctx.fillRect(this.x, this.y, this.width, this.height);
    };

    Cactus.prototype.update = function() {
        this.x -= gameSpeed;
    };

    var player = new Dino();

    function spawnObstacle() {
        if (Math.random() < 0.02 && (obstacles.length === 0 || obstacles[obstacles.length-1].x < GAME_WIDTH - 150)) {
            obstacles.push(new Cactus());
        }
    }

    function formatScore(n) {
        var s = n.toString();
        while (s.length < 5) s = "0" + s;
        return s;
    }

    function resetGame() {
        score = 0;
        obstacles = [];
        gameSpeed = 5;
        isPlaying = true;
        gameOverScreen.style.display = 'none';
        update();
    }

    function update() {
        if (!isPlaying) return;

        ctx.clearRect(0, 0, GAME_WIDTH, GAME_HEIGHT);

        ctx.beginPath();
        ctx.moveTo(0, GAME_HEIGHT - 5);
        ctx.lineTo(GAME_WIDTH, GAME_HEIGHT - 5);
        ctx.strokeStyle = "#535353";
        ctx.stroke();

        player.update();
        player.draw();

        spawnObstacle();

        for (var i = obstacles.length - 1; i >= 0; i--) {
            obstacles[i].update();
            obstacles[i].draw();

            if (player.x < obstacles[i].x + obstacles[i].width &&
                player.x + player.width > obstacles[i].x &&
                player.y < obstacles[i].y + obstacles[i].height &&
                player.y + player.height > obstacles[i].y) {
                
                isPlaying = false;
                if (score > highScore) highScore = score;
                highDisplay.innerHTML = formatScore(highScore);
                gameOverScreen.style.display = 'block';
            }

            if (obstacles[i].x + obstacles[i].width < 0) {
                obstacles.splice(i, 1);
                score++;
                scoreDisplay.innerHTML = formatScore(score);
                if (score % 10 === 0) gameSpeed += 0.2;
            }
        }

        var requestAnimationFrame = window.requestAnimationFrame || 
                                   window.webkitRequestAnimationFrame || 
                                   window.mozRequestAnimationFrame || 
                                   function(c) { setTimeout(c, 1000/60); };
        requestAnimationFrame(update);
    }

    function handleInput(e) {
        if (!isPlaying) {
            resetGame();
        } else {
            player.jump();
        }
        if (e && e.type === 'touchstart') e.preventDefault();
    }

    window.addEventListener('keydown', function(e) {
        if (e.keyCode === 32 || e.keyCode === 38) handleInput();
    });

    canvas.addEventListener('touchstart', handleInput);

    update();
})();
</script>
</body>
</html>