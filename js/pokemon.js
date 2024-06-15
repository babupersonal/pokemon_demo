document.addEventListener('DOMContentLoaded', function() {

    //loading加載
    var hid= document.getElementById('hidden');
    var duck = document.getElementById('duck');
    setTimeout(function() {
        hid.style.display = 'block';
        duck.style.display = 'none';
    }, 3000);

    const cat = document.getElementById('cat');
    const moneyElement = document.querySelector('.money p');
    const backpackList = document.getElementById('backpack-content');
    let money = initialMoney;
    let frameCounter = 0;
    const frames = [
        'img/1.png', 'img/2.png', 'img/3.png', 'img/4.png', 'img/5.png', 'img/6.png',
        'img/7.png', 'img/8.png', 'img/9.png', 'img/10.png', 'img/11.png', 'img/12.png'
    ];
    let currentFrame = 0;

    function updateFrame() {
        cat.src = frames[currentFrame];
        currentFrame = (currentFrame + 1) % frames.length;
        frameCounter++;
        if (frameCounter % 10 === 0) {
            increaseMoney();
        }
    }

    function increaseMoney() {
        money += 1;
        moneyElement.textContent = money;
        updateMoneyInDatabase(money);
    }

    function updateMoneyInDatabase(newMoney) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "pokemon.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send("money=" + newMoney);
    }

    setInterval(updateFrame, 200);

    const bg = document.querySelector('.bg');
    let bgPosition = -50;

    function moveBackground() {
        bgPosition += 0.5;
        if (bgPosition >= 0) {
            bgPosition = -50;
        }
        bg.style.transform = `translateX(${bgPosition}%)`;
    }

    setInterval(moveBackground, 100);

    // 背包点击事件
    const backpack = document.querySelector('.backpack-bg');

    window.openbackpack = function() {
        backpack.classList.toggle('active');
        if (backpack.classList.contains('active')) {
            loadBackpack();
        }
    }

    // 登出
    window.logout = function() {
        window.location.href = 'logout.php';
    }

    // 商店载入更多
    const storeBg = document.querySelector('.store-bg');
    const pokemonList = document.getElementById('pokemon-list');
    let offset = 20; // 初始偏移量设为20，因为PHP已经加载了前20条数据
    let loading = false;
    let hasMoreData = true;
    // 商店擊事件
    window.openstore = function() {
        storeBg.classList.toggle('active');
    }
    //分工點擊事件
    window.toggleWork = function() {
        let workBg = document.getElementById('workBg');
        if (workBg.classList.contains('active')) {
            workBg.classList.remove('active');
            setTimeout(() => {
                workBg.style.display = 'none'; // 過渡結束後設為不可見
            }, 500); // 與過渡效果時間一致
        } else {
            workBg.style.display = 'block'; // 先設為可見
            setTimeout(() => {
                workBg.classList.add('active');
            }, 10); // 延遲以確保過渡效果
        }
    }
    
    
    function loadMorePokemon() {
        if (loading || !hasMoreData) return;
        loading = true;
        console.log('Loading more data... Offset:', offset);

        const xhr = new XMLHttpRequest();
        xhr.open("GET", "load_more_pokemon.php?offset=" + offset, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                console.log('Response Text:', xhr.responseText);
                if (xhr.status === 200) {
                    try {
                        const newPokemon = JSON.parse(xhr.responseText);
                        console.log('Parsed data:', newPokemon);
                        if (newPokemon.length === 0) {
                            hasMoreData = false;
                            console.log('No more data available.');
                        } else {
                            newPokemon.forEach(function(pokemon) {
                                const card = document.createElement('div');
                                card.className = 'cards';

                                const imgUrl = pokemon.img_url.replace("file/d/", "uc?export=view&id=").replace("/view?usp=sharing", "");

                                card.innerHTML = `
                                    <ul>
                                        <li>名稱: ${pokemon.名稱}</li>
                                        <li>屬性1: ${pokemon.屬性一}</li>
                                        <li>屬性2: ${pokemon.屬性二}</li>
                                        <li>戰鬥力: ${pokemon.總數值}</li>
                                    </ul>
                                    <img src="${imgUrl}" alt="${pokemon.名稱}">
                                    <form method="post" action="" class="c buy-form">
                                        <input type="hidden" name="pokemon_id" value="${pokemon.編號}">
                                        <button type="submit" class="buy">購買</button>
                                    </form>
                                `;
                                pokemonList.appendChild(card);
                            });
                            offset += 20;
                            console.log('Data loaded. New offset:', offset);
                        }
                    } catch (e) {
                        console.error('Error parsing JSON:', e);
                        console.log('Response text:', xhr.responseText);
                    }
                } else {
                    console.error('Request failed. Status:', xhr.status);
                }
                loading = false;
                console.log('Loading finished.');
            }
        };
        xhr.send();
    }

    // 移除初始加载20条数据的部分
    // let initialLoad = true;
    // function initLoadMorePokemon() {
    //     if (initialLoad) {
    //         loadMorePokemon();
    //         initialLoad = false; // 確保只初始化一次
    //     }
    // }

    // initLoadMorePokemon();

    // 滚动事件监听器
    storeBg.addEventListener('scroll', function() {
        if (storeBg.scrollTop + storeBg.clientHeight >= storeBg.scrollHeight - 1) {
            loadMorePokemon();
        }
    });

    // 监听store搜索输入框的输入事件
    document.getElementById('storeSearchInput').addEventListener('input', function() {
        const searchText = this.value.toLowerCase();
        const cards = document.querySelectorAll('#pokemon-list .cards');
        cards.forEach(function(card) {
            const name = card.querySelector('li:first-child').textContent.toLowerCase();
            if (name.includes(searchText)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });

    // 图档文字显示
    document.getElementById('imageFile').addEventListener('change', function() {
        var fileName = this.files[0].name;
        var label = document.querySelector('.image-label');
        label.textContent = fileName;
    });

    // 音档文字显示
    document.getElementById('musicFile').addEventListener('change', function() {
        var fileName = this.files[0].name;
        var label = document.querySelector('.music-label');
        label.textContent = fileName;
    });

    // 文字恢复原样
    function resetUploadForm() {
        document.getElementById('uploadForm').reset();
        document.querySelector('.image-label').textContent = '选择封面图片';
        document.querySelector('.music-label').textContent = '选择档案';
    }

    // 处理表单提交
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "upload.php", true);

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                var statusDiv = document.getElementById('uploadStatus');
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        statusDiv.textContent = response.message;
                        // 动态更新页面内容
                        addMusicToPage(response.music);
                    } else {
                        statusDiv.textContent = "上传失败: " + response.message;
                    }
                } else {
                    statusDiv.textContent = "上传失败: " + xhr.statusText;
                }
            }
        };

        xhr.send(formData);
    });

    var soundPlates = document.querySelectorAll('.circle');
    var audios = document.querySelectorAll('.song');

    for (var x = 0; x < audios.length; x++) {
        (function(index) {
            var soundPlate = soundPlates[index];
            var soundWave = soundPlate.querySelector('.sound-wave');
            var audio = audios[index];

            bindAudioEvents(audio, soundPlate, soundWave);
        })(x);
    }

    var muswiper = new Swiper('.muswiper', {
        direction: 'vertical',
        slidesPerView: 1,
        spaceBetween: 30,
        mousewheel: true,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
    });

    function bindAudioEvents(audio, soundPlate, soundWave) {
        audio.addEventListener('play', function() {
            soundPlate.style.animation = 'rotate 8s linear infinite';
            soundWave.style.opacity = '1';
            soundWave.style.animationPlayState = 'running'; // 启动音律环动画
        });
    
        audio.addEventListener('pause', function() {
            soundPlate.style.animation = 'none';
            soundWave.style.opacity = '0';
            soundWave.style.animationPlayState = 'paused'; // 暂停音律环动画
        });
    
        audio.addEventListener('ended', function() {
            soundPlate.style.animation = 'none';
            soundWave.style.opacity = '0';
            soundWave.style.animationPlayState = 'paused'; // 暂停音律环动画
        });
    }

    function addMusicToPage(music) {
        var swiperWrapper = document.querySelector('.swiper-wrapper');

        var newSlide = document.createElement('div');
        newSlide.className = 'swiper-slide c';

        var soundPlate = document.createElement('div');
        soundPlate.className = 'circle c my-3';
        soundPlate.style.backgroundImage = `url(${music.image_path})`;
        soundPlate.style.backgroundSize = 'cover';
        soundPlate.style.backgroundPosition = 'top';

        var centerPoint = document.createElement('div');
        centerPoint.className = 'center-point';

        var soundWave = document.createElement('div');
        soundWave.className = 'sound-wave';

        soundPlate.appendChild(centerPoint);
        soundPlate.appendChild(soundWave); // 添加音律环

        var nameDiv = document.createElement('div');
        nameDiv.className = 'name my-3';
        var nameP = document.createElement('p');
        nameP.textContent = music.file_name;
        nameDiv.appendChild(nameP);

        var audio = document.createElement('audio');
        audio.controls = true;
        audio.className = 'my-3 song';
        var source = document.createElement('source');
        source.src = music.file_path;
        source.type = 'audio/mpeg';
        audio.appendChild(source);

        newSlide.appendChild(soundPlate);
        newSlide.appendChild(nameDiv);
        newSlide.appendChild(audio);

        swiperWrapper.appendChild(newSlide);

        // 重新初始化 Swiper
        var muswiper = new Swiper('.muswiper', {
            direction: 'vertical',
            slidesPerView: 1,
            spaceBetween: 30,
            mousewheel: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
        });

        // 绑定音频事件
        bindAudioEvents(audio, soundPlate, soundWave);
    }
    
    // 加载背包数据
    function loadBackpack() {
        fetch('pokemon.php?action=load_backpack')
            .then(response => response.text())  // 先处理为文本
            .then(text => {
                try {
                    const data = JSON.parse(text);  // 尝试将文本解析为JSON
                    var backpackBg = document.getElementById('backpack-content');
                    backpackBg.innerHTML = '';
                    data.forEach(function(pokemon) {
                        var pokemonCard = document.createElement('div');
                        pokemonCard.className = 'cards';
                        pokemonCard.innerHTML = `
                            <img src="${pokemon.img_url}" alt="${pokemon.名稱}">
                            <ul>
                                <li>名稱: ${pokemon.名稱}</li>
                                <li>屬性1: ${pokemon.屬性一}</li>
                                <li>屬性2: ${pokemon.屬性二}</li>
                            </ul>
                        `;
                        backpackBg.appendChild(pokemonCard);
                    });
                } catch (e) {
                    console.error('Error parsing JSON:', e);
                    console.log('Response text:', text);  // 输出响应文本以便调试
                }
            })
            .catch(error => console.error('Fetch error:', error));
    }

    document.getElementById('searchInput').addEventListener('input', function() {
        const searchText = this.value.toLowerCase();
        const cards = document.querySelectorAll('#backpack-content .cards');
        cards.forEach(function(card) {
            const name = card.querySelector('li:first-child').textContent.toLowerCase();
            if (name.includes(searchText)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
    loadBackpack();

    // 购买宝可梦
    pokemonList.addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const pokemonId = form.querySelector('input[name="pokemon_id"]').value;
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "pokemon.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        console.log('Purchase successful:', response);
                        // 移除已购买的宝可梦卡片
                        form.closest('.cards').remove();
                        // 更新背包显示
                        const backpackCard = document.createElement('div');
                        backpackCard.className = 'cards';
                        backpackCard.innerHTML = `
                            <img src="${response.pokemon.img_url}" alt="${response.pokemon.名稱}">
                            <ul>
                                <li>名稱: ${response.pokemon.名稱}</li>
                                <li>屬性1: ${response.pokemon.屬性一}</li>
                                <li>屬性2: ${response.pokemon.屬性二}</li>
                            </ul>
                        `;
                        backpackList.appendChild(backpackCard);
                        // 更新金钱显示
                        document.getElementById('money-amount').textContent = response.new_money;
                        money = response.new_money; // 更新内存中的金钱值
                    } else {
                        console.error('Purchase failed:', response);
                    }
                } catch (e) {
                    console.error('Error parsing JSON:', e);
                }
            }
        };
        xhr.send(`pokemon_id=${pokemonId}`);
    });

    //音樂界面開關
    window.zoomOutMusic = function() {
        const musicDiv = document.querySelector('.music');
        const zoomInBtn = document.querySelector('.zoom-in');
        musicDiv.classList.add('collapsed');
        zoomInBtn.style.display = 'flex';

        // 隐藏其他元素
        const elementsToHide = document.querySelectorAll('.info, .swiper, #uploadForm');
        elementsToHide.forEach(element => {
            element.style.display = 'none';
        });
    }

    window.zoomInMusic = function() {
        const musicDiv = document.querySelector('.music');
        const zoomInBtn = document.querySelector('.zoom-in');
        musicDiv.classList.remove('collapsed');
        zoomInBtn.style.display = 'none';

        // 恢复显示其他元素
        const elementsToShow = document.querySelectorAll('.info, .swiper, #uploadForm');
        elementsToShow.forEach(element => {
            element.style.display = 'flex';
        });
    }

});
