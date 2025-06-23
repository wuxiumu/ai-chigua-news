// /js/common.js

// 头部导航、LOGO、Banner、底部、热门文章/标签渲染
window.AppConfig = {
    NAV_MENU: [
        { label: '首页', to: 'home.html' },
        { label: '爆料分类', to: 'home.html' },
        { label: '明星爆料', to: 'home.html' },
        { label: '娱乐新闻', to: 'home.html' },
        { label: '年度盘点', children: [
                { label: '2024盘点', to: '/#/tags/2024' },
                { label: '2023盘点', to: '/#/tags/2023' }
            ] },
        { label: '关于我们', to: '/#/tags/about.html' }
    ],
    LOGO_TEXT: '吃瓜爆料',
    FOOTER_COPYRIGHT: '© 2024 明星爆料吃瓜资讯 All Rights Reserved.',
    BANNER_LIST: [
        { img: 'https://archive.biliimg.com/bfs/archive/80cfd240828f7f59086e6e87e61497eb93e69962.png', link: 'https://wuxiumu.github.io' },
        { img: 'https://archive.biliimg.com/bfs/archive/9d108eb5393a1e773c49acc514c04f638af8d789.png', link: 'https://wuxiumu.github.io' }
    ],
    HOT_ARTICLES: [
        { id: '1', title: '年度爆料合集' }, { id: '2', title: '某某翻车实录' }
    ],
    HOT_TAGS: ['八卦','爆料','明星','娱乐新闻','吃瓜','年度盘点','幕后趣闻']
};

function renderMenu(menus, level=0) {
    let html = `<div class="nav-list${level===0?'':' dropdown-menu'}">`;
    menus.forEach(menu => {
        let hasChild = menu.children && menu.children.length;
        let active = '';
        let hash = decodeURIComponent(location.hash || '');
        if(menu.to && (
            location.pathname+location.search === menu.to ||
            (hash.startsWith('#/tags/') && menu.to.indexOf('tag=')>-1 && hash.indexOf('/tags/'+encodeURIComponent(menu.to.split('tag=')[1]))>-1)
        )) active = ' active';
        if(hasChild){
            html += `<div class="nav-dropdown${active}">
              <a class="nav-link${active}" href="javascript:void(0)">${menu.label} &#9662;</a>
              ${renderMenu(menu.children, level+1)}
            </div>`;
        }else{
            html += `<a class="nav-link${active}" href="${menu.to||'#'}">${menu.label}</a>`;
        }
    });
    html += '</div>';
    return html;
}
function bindMenuEvents() {
    const menuBtn = document.getElementById('menuBtn');
    const navList = document.querySelector('.nav-list');
    menuBtn && (menuBtn.onclick = e => {
        navList.classList.toggle('open');
        e.stopPropagation();
    });
    navList && (navList.onclick = e => {
        if(window.innerWidth<=600) {
            let t = e.target;
            if(t.classList.contains('nav-link') && t.nextElementSibling && t.nextElementSibling.classList.contains('dropdown-menu')){
                // 收起其他已展开的下拉
                document.querySelectorAll('.nav-dropdown.open').forEach(el=>el!==t.parentNode && el.classList.remove('open'));
                t.parentNode.classList.toggle('open');
                e.preventDefault();
                e.stopPropagation();
                return false;
            } else if(t.classList.contains('nav-link')) {
                navList.classList.remove('open');
            }
        }
    });
    window.onclick = function(e){
        if(window.innerWidth<=600 && navList.classList.contains('open') && !navList.contains(e.target) && e.target!==menuBtn){
            navList.classList.remove('open');
            document.querySelectorAll('.nav-dropdown.open').forEach(el=>el.classList.remove('open'));
        }
    };
    window.onresize = function(){
        if(window.innerWidth>600) {
            navList.classList.remove('open');
            document.querySelectorAll('.nav-dropdown.open').forEach(el=>el.classList.remove('open'));
        }
    };
}

// 渲染 LOGO、导航、banner
function renderHeader() {
    document.getElementById('logo').innerText = AppConfig.LOGO_TEXT;
    document.getElementById('nav').innerHTML = renderMenu(AppConfig.NAV_MENU);
    bindMenuEvents();
    // Banner
    let bannerHTML = '';
    let BL = AppConfig.BANNER_LIST;
    if(BL.length===1){
        bannerHTML = `<a href="${BL[0].link}" target="_blank"><img referrerpolicy="no-referrer"  src="${BL[0].img}" alt="banner"></a>`;
    } else if(BL.length>1){
        bannerHTML = '<div style="position:relative;"><img referrerpolicy="no-referrer" id="bannerImg" src="'+BL[0].img+'" style="width:100%;display:block;"/><a id="bannerLink" href="'+BL[0].link+'" target="_blank" style="position:absolute;top:0;left:0;width:100%;height:100%;"></a></div>';
    }
    if(document.getElementById('banner')) document.getElementById('banner').innerHTML = bannerHTML;
    if(BL.length>1 && document.getElementById('bannerImg')){
        let idx = 0;
        setInterval(()=>{
            idx = (idx+1)%BL.length;
            document.getElementById('bannerImg').src = BL[idx].img;
            document.getElementById('bannerLink').href = BL[idx].link;
        },3500);
    }
}

// 底部
function renderFooter() {
    if(document.getElementById('footer'))
        document.getElementById('footer').innerText = AppConfig.FOOTER_COPYRIGHT;
}

// 热门文章、标签
function renderHotArticles() {
    if(!document.getElementById('hot-articles')) return;
    let html = '';
    AppConfig.HOT_ARTICLES.forEach(a => {
        html += `<li class="hot-item" onclick="location.href='article.html?id=${encodeURIComponent(a.id)}'">${a.title}</li>`;
    });
    document.getElementById('hot-articles').innerHTML = html || '<li style="color:#bbb;">暂无</li>';
}
function renderHotTags(onClick) {
    if(!document.getElementById('hot-tags')) return;
    let html = '';
    AppConfig.HOT_TAGS.forEach(t => {
        html += `<span class="hot-tag" data-tag="${t}">${t}</span>`;
    });
    document.getElementById('hot-tags').innerHTML = html;
    // 可传入回调用于页面自定义标签行为
    if(onClick){
        document.querySelectorAll('.hot-tag').forEach(tagEl=>{
            tagEl.onclick = onClick;
        });
    }
}

// 通用 hash 路由解析
function parseHash() {
    let hash = location.hash || '';
    let tag = '';
    let page = 1;
    if(hash.startsWith('#/tags/')) {
        tag = decodeURIComponent(hash.slice(7).split('?')[0]);
    }
    let m = hash.match(/[?&]page=(\d+)/);
    if(m) page = parseInt(m[1]);
    return { tag, page };
}
function setHash(tag, page) {
    let hash = tag ? `#/tags/${encodeURIComponent(tag)}` : '#/';
    if(page && page>1) hash += (hash.indexOf('?')>-1 ? '&' : '?') + 'page=' + page;
    location.hash = hash;
}