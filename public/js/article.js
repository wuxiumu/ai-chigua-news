window.onload = function() {
    renderHeader();
    renderFooter();
    renderHotArticles();
    renderHotTags(function(e){
        setHash(this.innerText, 1);
        location.href = 'home.html'+location.hash;
        e.stopPropagation();
    });

    // 获取文章ID
    const urlParams = new URLSearchParams(location.search);
    const id = urlParams.get('id');
    if(!id) return;

    // 拉取详情
    fetch('/api/articleinfo/' + encodeURIComponent(id))
        .then(res=>res.json())
        .then(res=>{
            if(res.code === 0 && res.data){
                document.getElementById('article-title').innerText = res.data.title;
                document.getElementById('article-meta').innerHTML = `
                <span>${res.data.date || ''}</span>
                <span class="item-tags">${(res.data.tags||[]).map(tag=>`<span class="tag">${tag}</span>`).join('')}</span>
            `;
                // 内容可转 html（如用 marked、showdown，后端可直接返回html）
                document.getElementById('article-content').innerHTML = res.data.content_html || res.data.content || '';
            } else {
                document.getElementById('article-title').innerText = '未找到文章';
                document.getElementById('article-content').innerHTML = '';
            }
        });
};