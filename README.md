
# 🍉 明星爆料娱乐八卦资讯 - 极速无刷吃瓜聚合站

> 一个为吃瓜群众和娱乐爱好者打造的高性能资讯平台。后端采用 Slim PHP + 文件系统极速驱动，前端极致轻量，支持标签、分页、SEO、移动端自适应，内容和页面全支持 Markdown。  
> 灵感来自知乎/微博热榜，特色是**极致简单、极致开放、极致可定制**。



---

## ✨ 项目亮点

- **极速架构**：无数据库，纯 PHP 文件系统 + JSON/Markdown，1核512M 云服务器也能秒开。
- **极简CMS**：支持内容 Markdown 编辑，前后端分离，Vue3 后台管理、文章池/标题池全自动。
- **无刷新体验**：AJAX 切换标签、分页，SPA 级流畅。
- **全栈可改造**：目录结构清晰，适合二开、嵌入AI内容生成、批量爬虫、自动聚合等玩法。
- **SEO 极致优化**：自动 sitemap、结构化数据、canonical、主动推送、全端友好。
- **高度可定制**：菜单/标签/Banner/版权等全部支持 JS/ENV 配置。

---

## 📦 项目预览

<img src="https://archive.biliimg.com/bfs/archive/c9ee0e351384d7a2f8b343bf7abfc33016669315.png"  referrerpolicy="no-referrer">

---

## 🚀 快速启动

**1. 克隆仓库**
```bash
git clone https://github.com/wuxiumu/ai-chigua-news.git
cd ai-chigua-news
```

**2. 安装 PHP 依赖**

```
composer install
# 推荐 PHP >=7.4
```

**3. 启动本地服务**

```
composer start
# 或 nginx/apache 指向 public/
```

**4. 启动前端开发（可选后台）**

```
cd star-gossip-admin
npm i
npm run dev
# 后台地址 http://localhost:5173
```



------



## **🖼️ 页面演示**





- **首页（标签/分页无刷新）**
  <img src="https://archive.biliimg.com/bfs/archive/db575496103d137753e7faecb320b51d1b54b182.png"  referrerpolicy="no-referrer">




- **详情页（支持 Markdown 展示）**

<img src="https://archive.biliimg.com/bfs/archive/87e45d07459e7e6c505ba9d7c88bebc8568b380b.png"  referrerpolicy="no-referrer">



- **后台管理-登陆（Vue3）**

<img src="https://archive.biliimg.com/bfs/archive/66d9875527c4d84cdd9bebadb6f5cdd1bf26cd86.png"  referrerpolicy="no-referrer">

- **后台管理-文章管理**
  <img src="https://archive.biliimg.com/bfs/archive/54ed6746780ba1e822b38c01b557a48a3174092e.png"  referrerpolicy="no-referrer">

- **后台管理-文章编辑**
  <img src="https://archive.biliimg.com/bfs/archive/6da94a76db2f1ca00e02ec4b038a517f7894a823.png"  referrerpolicy="no-referrer">


- **后台管理-标题池子管理**
  <img src="https://archive.biliimg.com/bfs/archive/23ea2c98afb3347dd6ec17dd7ab80885a1a0005a.png"  referrerpolicy="no-referrer">

- **后台管理-历史热点标题聚合**
  <img src="https://archive.biliimg.com/bfs/archive/68864a5595e61020f3c0aec044ade5dd2e4e368c.png"  referrerpolicy="no-referrer">
------





## **🔥 核心功能**





- 首页分页、标签切换、热门文章/标签、移动端自适应
- 文章详情页：内容 Markdown 自动渲染
- 支持 sitemap.xml、结构化数据、SEO 全面优化
- 标题管理池：支持批量导入、AI内容生成、自动聚合
- 后台管理（Vue3/Pinia）：文章增删改查、用户管理、JWT 鉴权
- 高可用配置（.env），站点、关键词、Banner 一键调整
- 一键部署，云服务器极简适配





------





## **🛠️ 技术栈**





- **后端**：Slim Framework 4、PHP 7.4+、无数据库/本地文件系统
- **前端**：原生 JS/Vue3（后台）、Element Plus、Pinia
- **内容格式**：Markdown/JSON
- **API**：RESTful + JWT 鉴权
- **SEO**：自动站点地图、结构化数据、主动推送





------





## **🧑‍💻 开发与贡献**





- 拉 PR 前请先 fork 并创建 feature 分支
- 推荐提交说明用中文，注释尽量中英文
- issue/提问/feature请求都欢迎





------





## **📄 License**





[MIT License](./LICENSE)



------





## **🙏 致谢**





感谢各位吃瓜群众、爆料网友，感谢开源生态和明星八卦的源源不断。



------



> **灵感来自生活，源码服务吃瓜。Star 一下，瓜不嫌多！🍉**



> 后面继续优化，先记个备忘录。

- 分页-标签-热门文章/标签-移动端自适应
- 定时任务-自动更新热门文章/标签/热门爆料

## **1.** **AI 配置管理，自定义ai模型**
- 阿里云AI配置
- GPT模型配置
- 自定义模型训练
------

## **2.** **自定义主题，主题样式支持流量分发**
- 自定义主题模块
- 主题样式支持流量分发
- 主题样式优化，提升用户体验
------

## **3.** **SEO与流量极致优化**


- **SSR（服务端渲染）或静态化首页/列表**，提升搜索引擎抓取友好度
- **schema.org 结构化数据**，热门爆料/作者/标签等全部加 rich snippets
- **每日/每周爆料榜单自动生成并推送百度/Google**
- **自定义 sitemap、robots.txt 自动更新**

------



## **4.** **内容聚合与数据自动化**

- **自动爬取/聚合外部爆料源**（今日头条、微博热搜、知乎等），内容清洗统一格式
- **关键词热点追踪+自动生成专题页**
- **AI辅助写稿：自动补全、内容润色、爆料摘要自动生成**