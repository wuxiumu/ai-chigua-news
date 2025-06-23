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
    if(!id) {
        document.getElementById('title').innerText = '未找到文章';
        return;
    }

    // 拉取详情
    fetch('/api/articleinfo/' + encodeURIComponent(id))
        .then(res=>res.json())
        .then(res=>{
            if(res.code === 0 && res.data){
                document.getElementById('title').innerText = res.data.title || '';
                document.getElementById('meta').innerHTML = `
                <span>${res.data.date ? res.data.date.slice(0,16) : ''}</span>
                <span class="item-tags">${(res.data.tags||[]).map(tag=>`<span class="tag">${tag}</span>`).join('')}</span>
            `;
                // markdown渲染
                let content = res.data.content || '';
                if(window.marked){
                    document.getElementById('content').innerHTML = marked.parse(content);
                }else{
                    document.getElementById('content').innerText = content;
                }
            } else {
                document.getElementById('title').innerText = '未找到文章';
                document.getElementById('content').innerHTML = '';
            }
        });

    // 标签点击跳转
    document.addEventListener('click', function(e){
        if(e.target.classList.contains('tag')){
            setHash(e.target.innerText, 1);
            location.href = 'home.html'+location.hash;
            e.stopPropagation();
        }
    });
};