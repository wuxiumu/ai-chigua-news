
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

## 📦 项目结构
```php/
├── public/
│   └── index.php            // Slim入口
├── app/
│   ├── routes.php
│   ├── controllers/
│   │   ├── ArticleController.php
│   │   └── ...
│   ├── services/
│   │   └── ArticleService.php
│   └── helpers/
│       ├── MarkdownHelper.php
│       └── SeoHelper.php
├── data/
│   ├── articles/
│   │   └── 20240622_xxxxx.md
│   └── index.json
├── composer.json
└── ...

```
### star-gossip-admin
```js
src/
├── api/               // API接口定义
│   └── article.js
├── components/
│   └── MarkdownEditor.vue
├── views/
│   ├── ArticleList.vue
│   ├── ArticleEdit.vue
│   └── Login.vue
├── store/
│   └── user.js
├── router/
│   └── index.js
├── App.vue
└── main.js
```

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





- **详情页（支持 Markdown 展示）**





- **后台管理（Vue3）**





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
