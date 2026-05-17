# 🚀 Deployment Guide for Smart Feedback System

## 部署到Web空间的步骤

### 1. 上传文件
将所有文件上传到你的web空间（通常是 public_html 或 www 目录）

### 2. 配置数据库设置

编辑 `inc/config.php` 文件：

```php
<?php
return [
    'driver' => 'mysql',
    
    // 🔧 重要：设置你的base_url
    // 如果项目在根目录: ''
    // 如果在子文件夹: '/subfolder_name'
    'base_url' => '',  // 👈 改成空字符串（如果在域名根目录）
    9
    'mysql' => [
        'host'   => '127.0.0.1',        // 你的数据库主机
        'port'   => 3306,                // 数据库端口
        'dbname' => 'your_database',     // 👈 改成你的数据库名
        'user'   => 'your_username',     // 👈 改成你的数据库用户名
        'pass'   => 'your_password'      // 👈 改成你的数据库密码
    ],
    
    'sqlite' => [
        'path' => __DIR__ . '/../database/feedback.db'
    ]
];
```

### 3. 设置文件权限

```bash
chmod 755 storage/
chmod 755 storage/profile_pics/
```

或者通过FTP客户端（FileZilla等）：
- 右键点击文件夹 → Properties → Permissions
- 设置 storage 和 storage/profile_pics 为 755 或 777

### 4. 运行数据库设置

访问：`http://yourdomain.com/setup.php`

这会：
- 创建所有必需的数据库表
- 创建默认管理员账户（admin/admin123）

### 5. 删除 setup.php

⚠️ **重要**：设置完成后立即删除 `setup.php` 文件以确保安全！

```bash
rm setup.php
```

### 6. 测试系统

1. 访问你的网站首页
2. 登录管理员账户（admin/admin123）
3. 立即修改管理员密码！
4. 测试上传头像功能

---

## 🐛 常见问题排查

### 问题：看不到头像图片

**原因**：base_url 配置不正确

**解决方法**：

1. 检查 `inc/config.php` 中的 `base_url` 设置
2. 如果你的网站是 `yourdomain.com/`，设置：
   ```php
   'base_url' => '',
   ```
3. 如果你的网站是 `yourdomain.com/smart_feedback/`，设置：
   ```php
   'base_url' => '/smart_feedback',
   ```

### 问题：头像上传失败

**检查**：
1. storage/profile_pics/ 文件夹权限是否正确（755或777）
2. PHP 文件上传限制（upload_max_filesize, post_max_size）

### 问题：数据库连接失败

**检查**：
1. 数据库名称、用户名、密码是否正确
2. 数据库用户是否有足够的权限
3. 数据库主机地址（可能是 localhost 或 127.0.0.1）

### 问题：页面显示空白

**解决**：
1. 检查 PHP 错误日志
2. 在 `inc/db.php` 顶部确保有：
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

---

## 📝 本地与生产环境对比

| 设置 | 本地开发（XAMPP） | 生产环境（Web空间） |
|------|------------------|-------------------|
| base_url | `/smart_feedback` | `''` (通常) |
| Database Host | `127.0.0.1` | 提供商指定 |
| Database Name | `dramranc_smartfeedback` | 你的数据库名 |
| File Permissions | 自动 | 需要手动设置 |
| Error Display | 开启 | 关闭（生产） |

---

## 🔒 安全建议

1. ✅ 删除 setup.php
2. ✅ 修改默认管理员密码
3. ✅ 在生产环境关闭错误显示
4. ✅ 使用强密码保护数据库
5. ✅ 定期备份数据库
6. ✅ 保持 PHP 版本更新

---

## 💡 提示

- 如果不确定 base_url，可以留空 `''`，系统会尝试自动检测
- 上传文件前先在本地测试所有功能
- 保存原始的 config.php 作为备份
- 使用 .gitignore 避免提交敏感配置到版本控制
