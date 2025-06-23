// home.js

window.onload = function() {
    // 公共头部、菜单、footer、热门等
    renderHeader();
    renderFooter();
    renderHotArticles();
    renderHotTags(function(e){
        setHash(this.innerText, 1);
        // 只改变hash，由window.onhashchange自动刷新列表
        e.stopPropagation();
    });

    // 分页+标签核心逻辑
    function renderList(list) {
        let html = '';
        list.forEach(item => {
            html += `<li class="item">
                <div class="item-title" onclick="location.href='article.html?id=${encodeURIComponent(item.id)}'">${item.title}</div>
                <div class="item-desc">${item.desc||''}</div>
                <div class="item-meta">
                    <span>${item.date ? item.date.slice(0,16) : ''}</span>
                    <span class="item-tags">
                        ${(item.tags||[]).map(tag=>`<span class="tag" data-tag="${tag}">${tag}</span>`).join('')}
                    </span>
                </div>
            </li>`;
        });
        document.getElementById('list').innerHTML = html || '<li style="color:#aaa;text-align:center;padding:40px;">暂无内容</li>';
        // 标签点击跳转
        document.querySelectorAll('.item-tags .tag').forEach(tagEl => {
            tagEl.onclick = function(e){
                setHash(this.innerText, 1);
                e.stopPropagation();
            };
        });
    }

    function renderPager(current, total) {
        let html = '';
        if(total > 1) {
            html += `<div style="text-align:center;margin:16px 0 0 0;">`;
            if(current > 1) html += `<button class="pager-btn" id="prevPage">上一页</button>`;
            html += ` <span style="margin:0 12px;">${current} / ${total}</span> `;
            if(current < total) html += `<button class="pager-btn" id="nextPage">下一页</button>`;
            html += `</div>`;
        }
        let pagerDom = document.getElementById('pager');
        if(!pagerDom){
            pagerDom = document.createElement('div');
            pagerDom.id = 'pager';
            document.querySelector('.main').appendChild(pagerDom);
        }
        pagerDom.innerHTML = html;
        if(current > 1) document.getElementById('prevPage').onclick = ()=>setHash(currentTag, current-1);
        if(current < total) document.getElementById('nextPage').onclick = ()=>setHash(currentTag, current+1);
    }

    let currentTag = '', currentPage = 1, totalPages = 1, pageSize = 10;

    function loadList() {
        const { tag, page } = parseHash();
        currentTag = tag || '';
        let url = '/api/articlelist?page='+(page||1)+'&page_size='+pageSize;
        if(currentTag) url += '&tag='+encodeURIComponent(currentTag);
        fetch(url).then(res=>res.json()).then(res=>{
            if(res.code === 0 && res.data){
                renderList(res.data.list || []);
                currentPage = res.data.page || 1;
                pageSize = res.data.size || 10;
                totalPages = Math.ceil((res.data.total || 0) / pageSize) || 1;
                renderPager(currentPage, totalPages);
            }
        });
    }

    // hash变动自动加载
    window.onhashchange = loadList;
    loadList();
};