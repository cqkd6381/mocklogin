## 模拟登录网站[橙米网](https://www.chengmi.cn)（本项目为临时项目，将于2020/10/07日删除）
## 安装
- git clone https://github.com/cqkd6381/mocklogin.git
- 配置入口至index.php后，在浏览器访问项目地址即可查看运行结果（如运行结果显示类似当日QPS已达上限等提示，请更改项目中百度OCR的access_token或改日再试）
## 注意
- 因项目中使用了百度OCR网络图片文字识别接口，且以另外的方式获取百度OCR的access_token（并未在项目中获取），该access_token有效期至2020/10/22日到期，届时项目需提供新的access_token方可继续使用。
- 本项目中涉及的隐私配置并未放入.env文件中管理，在真实项目中，请将其中的账号、密码、URL等配置项放入.env中，并将.env放入.gitignore中。

## 项目中涉及的文档
- [获取Access Token](http://ai.baidu.com/ai-doc/REFERENCE/Ck3dwjhhu)
- [网络图片文字识别（含位置版）](https://cloud.baidu.com/doc/OCR/s/Nkaz574we)
