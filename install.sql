-- MySQL dump 10.13  Distrib 8.0.24, for Linux (x86_64)
--
-- Host: localhost    Database: justoj
-- ------------------------------------------------------
-- Server version	8.0.24

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `compileinfo`
--

DROP TABLE IF EXISTS `compileinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compileinfo`
(
    `solution_id` int       NOT NULL DEFAULT '0',
    `error`       text      NOT NULL,
    `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`solution_id`)
) COMMENT='编译错误信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compileinfo`
--

LOCK
TABLES `compileinfo` WRITE;
/*!40000 ALTER TABLE `compileinfo` DISABLE KEYS */;
/*!40000 ALTER TABLE `compileinfo` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `contest`
--

DROP TABLE IF EXISTS `contest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contest`
(
    `contest_id`     int          NOT NULL AUTO_INCREMENT,
    `title`          varchar(255)          DEFAULT NULL,
    `start_time`     datetime              DEFAULT NULL,
    `end_time`       datetime              DEFAULT NULL,
    `defunct`        char(1)      NOT NULL DEFAULT 'N',
    `description`    text,
    `private`        tinyint      NOT NULL DEFAULT '0',
    `langmask`       varchar(255) NOT NULL DEFAULT '*' COMMENT 'lang mark with 1,2,3\n* for all',
    `password`       varchar(50)           DEFAULT NULL,
    `type`           tinyint               DEFAULT NULL COMMENT '0竞赛 1作业',
    `is_need_enroll` tinyint               DEFAULT '0' COMMENT '比赛是否需要注册\n0不需要\n1需要\n',
    `creator_id`     varchar(255) NOT NULL COMMENT '比赛创建者id',
    `create_time`    timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time`    timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`contest_id`)
) COMMENT='作业/比赛';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contest`
--

LOCK
TABLES `contest` WRITE;
/*!40000 ALTER TABLE `contest` DISABLE KEYS */;
/*!40000 ALTER TABLE `contest` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `contest_enroll`
--

DROP TABLE IF EXISTS `contest_enroll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contest_enroll`
(
    `id`          int       NOT NULL AUTO_INCREMENT,
    `user_id`     varchar(255)       DEFAULT NULL,
    `contest_id`  int                DEFAULT NULL,
    `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) COMMENT='比赛注册';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contest_enroll`
--

LOCK
TABLES `contest_enroll` WRITE;
/*!40000 ALTER TABLE `contest_enroll` DISABLE KEYS */;
/*!40000 ALTER TABLE `contest_enroll` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `contest_problem`
--

DROP TABLE IF EXISTS `contest_problem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contest_problem`
(
    `problem_id`  int       NOT NULL DEFAULT '0',
    `contest_id`  int                DEFAULT NULL,
    `title`       char(200) NOT NULL DEFAULT '',
    `num`         int       NOT NULL DEFAULT '0',
    `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time` timestamp NULL DEFAULT NULL,
    KEY           `Index_contest_id` (`contest_id`)
) COMMENT='作业/比赛 题目列表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contest_problem`
--

LOCK
TABLES `contest_problem` WRITE;
/*!40000 ALTER TABLE `contest_problem` DISABLE KEYS */;
/*!40000 ALTER TABLE `contest_problem` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `contest_tourist`
--

DROP TABLE IF EXISTS `contest_tourist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contest_tourist`
(
    `id`          int                                                          NOT NULL AUTO_INCREMENT,
    `contest_id`  int                                                          NOT NULL,
    `user_id`     varchar(48) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `create_time` timestamp                                                    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time` timestamp                                                    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) COMMENT='比赛旅游队标记';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contest_tourist`
--

LOCK
TABLES `contest_tourist` WRITE;
/*!40000 ALTER TABLE `contest_tourist` DISABLE KEYS */;
/*!40000 ALTER TABLE `contest_tourist` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `email_codes`
--

DROP TABLE IF EXISTS `email_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_codes`
(
    `id`          bigint                                                        NOT NULL AUTO_INCREMENT,
    `email`       varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `code`        varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NOT NULL,
    `create_time` timestamp                                                     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time` timestamp                                                     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) COMMENT='邮箱验证码';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_codes`
--

LOCK
TABLES `email_codes` WRITE;
/*!40000 ALTER TABLE `email_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_codes` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `group`
--

DROP TABLE IF EXISTS `group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `group`
(
    `id`          int          NOT NULL AUTO_INCREMENT,
    `name`        varchar(255)          DEFAULT NULL,
    `owner_id`    varchar(255) NOT NULL,
    `type`        tinyint               DEFAULT NULL COMMENT '0public\n1private\n',
    `password`    varchar(255)          DEFAULT NULL,
    `description` text,
    `deleted`     tinyint      NOT NULL DEFAULT '0',
    `create_time` timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `update_time` timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) COMMENT='班级基础表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group`
--

LOCK
TABLES `group` WRITE;
/*!40000 ALTER TABLE `group` DISABLE KEYS */;
/*!40000 ALTER TABLE `group` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `group_announce`
--

DROP TABLE IF EXISTS `group_announce`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_announce`
(
    `id`          int NOT NULL AUTO_INCREMENT,
    `group_id`    int NOT NULL,
    `msg`         text,
    `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted`     tinyint      DEFAULT '0',
    `title`       varchar(255) DEFAULT NULL,
    `update_time` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY           `group_id` (`group_id`),
    CONSTRAINT `group_announce_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`)
) COMMENT='班级通知公告';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_announce`
--

LOCK
TABLES `group_announce` WRITE;
/*!40000 ALTER TABLE `group_announce` DISABLE KEYS */;
/*!40000 ALTER TABLE `group_announce` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `group_join`
--

DROP TABLE IF EXISTS `group_join`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_join`
(
    `id`          int         NOT NULL AUTO_INCREMENT,
    `user_id`     varchar(48) NOT NULL,
    `group_id`    int         NOT NULL,
    `status`      tinyint     NOT NULL COMMENT '0未处理\n1accepted\n2rejected',
    `deleted`     tinyint     NOT NULL DEFAULT '0',
    `create_time` timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time` timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY           `group_id` (`group_id`),
    CONSTRAINT `group_join_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`)
) COMMENT='班级加入信息表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_join`
--

LOCK
TABLES `group_join` WRITE;
/*!40000 ALTER TABLE `group_join` DISABLE KEYS */;
/*!40000 ALTER TABLE `group_join` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `group_task`
--

DROP TABLE IF EXISTS `group_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_task`
(
    `id`          int       NOT NULL AUTO_INCREMENT,
    `group_id`    int       NOT NULL,
    `title`       varchar(255)       DEFAULT NULL,
    `link`        varchar(1000)      DEFAULT NULL,
    `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `update_time` timestamp NULL DEFAULT NULL,
    `contest_id`  int                DEFAULT NULL,
    `deleted`     tinyint   NOT NULL DEFAULT '0' COMMENT '0正常\n1已删除',
    PRIMARY KEY (`id`),
    KEY           `group_id` (`group_id`),
    CONSTRAINT `group_task_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`)
) COMMENT='班级作业';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_task`
--

LOCK
TABLES `group_task` WRITE;
/*!40000 ALTER TABLE `group_task` DISABLE KEYS */;
/*!40000 ALTER TABLE `group_task` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `judge_client`
--

DROP TABLE IF EXISTS `judge_client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `judge_client`
(
    `id`            bigint                                                        NOT NULL AUTO_INCREMENT,
    `client_name`   varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `data_git_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    `create_time`   timestamp                                                     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time`   timestamp                                                     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `judge_client_client_name_uindex` (`client_name`)
) COMMENT='判题机信息及状态';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `judge_client`
--

LOCK
TABLES `judge_client` WRITE;
/*!40000 ALTER TABLE `judge_client` DISABLE KEYS */;
/*!40000 ALTER TABLE `judge_client` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `loginlog`
--

DROP TABLE IF EXISTS `loginlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loginlog`
(
    `id`          int          NOT NULL AUTO_INCREMENT,
    `user_id`     varchar(48)  NOT NULL DEFAULT '',
    `ip`          varchar(100)          DEFAULT NULL,
    `user_agent`  varchar(255) NOT NULL,
    `result`      tinyint      NOT NULL DEFAULT '0' COMMENT '0失败 1成功',
    `create_time` timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time` timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) COMMENT='登录日志';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loginlog`
--

LOCK
TABLES `loginlog` WRITE;
/*!40000 ALTER TABLE `loginlog` DISABLE KEYS */;
/*!40000 ALTER TABLE `loginlog` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `news`
(
    `id`          int         NOT NULL AUTO_INCREMENT,
    `user_id`     varchar(48) NOT NULL                                           DEFAULT '' COMMENT 'user_id',
    `title_cn`    varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `content_cn`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `title_en`    varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `content_en`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `defunct`     char(1)     NOT NULL                                           DEFAULT 'N',
    `create_time` timestamp   NOT NULL                                           DEFAULT CURRENT_TIMESTAMP,
    `update_time` timestamp   NOT NULL                                           DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) COMMENT='首页新闻';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK
TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `password_reset_links`
--

DROP TABLE IF EXISTS `password_reset_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_links`
(
    `id`          bigint                                                        NOT NULL AUTO_INCREMENT,
    `user_id`     varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `uuid`        varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `create_time` timestamp                                                     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time` timestamp                                                     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) COMMENT='重置密码链接';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_links`
--

LOCK
TABLES `password_reset_links` WRITE;
/*!40000 ALTER TABLE `password_reset_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_links` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `paste`
--

DROP TABLE IF EXISTS `paste`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `paste`
(
    `id`          int       NOT NULL AUTO_INCREMENT,
    `user_id`     varchar(48)        DEFAULT NULL,
    `lang`        varchar(255)       DEFAULT NULL,
    `code`        text,
    `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) COMMENT='Paste';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paste`
--

LOCK
TABLES `paste` WRITE;
/*!40000 ALTER TABLE `paste` DISABLE KEYS */;
/*!40000 ALTER TABLE `paste` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `privilege`
--

DROP TABLE IF EXISTS `privilege`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `privilege`
(
    `user_id`     char(48) NOT NULL DEFAULT '',
    `rightstr`    char(30) NOT NULL DEFAULT '',
    `defunct`     char(1)  NOT NULL DEFAULT 'N',
    `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time` timestamp NULL DEFAULT NULL
) COMMENT='权限控制';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `privilege`
--

LOCK
TABLES `privilege` WRITE;
/*!40000 ALTER TABLE `privilege` DISABLE KEYS */;
INSERT INTO `privilege`
VALUES ('ismdeep', 'administrator', 'N', '2018-05-14 14:24:02', NULL);
/*!40000 ALTER TABLE `privilege` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `problem`
--

DROP TABLE IF EXISTS `problem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `problem`
(
    `problem_id`    int          NOT NULL AUTO_INCREMENT,
    `title`         varchar(200) NOT NULL DEFAULT '',
    `description`   text,
    `input`         text,
    `output`        text,
    `sample_input`  text,
    `sample_output` text,
    `spj`           char(1)      NOT NULL DEFAULT '0',
    `hint`          text,
    `source`        varchar(100)          DEFAULT NULL,
    `in_date`       datetime              DEFAULT NULL,
    `time_limit`    int          NOT NULL DEFAULT '0',
    `memory_limit`  int          NOT NULL DEFAULT '0',
    `defunct`       char(1)      NOT NULL DEFAULT 'N',
    `accepted`      int                   DEFAULT '0',
    `submit`        int                   DEFAULT '0',
    `solved`        int                   DEFAULT '0',
    `owner_id`      varchar(48)           DEFAULT NULL,
    `tags`          varchar(255) NOT NULL COMMENT '题目标签',
    `create_time`   timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time`   timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`problem_id`)
) COMMENT='题目';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `problem`
--

LOCK
TABLES `problem` WRITE;
/*!40000 ALTER TABLE `problem` DISABLE KEYS */;
INSERT INTO `problem`
VALUES (1000, 'Problem A+B (I)',
        '<p>给出两个不大于100的正整数a和ｂ，要你输出a+b的值。</p>\n\n<p>&nbsp;</p>\n\n<p>下面将展示出几种不同编程语言下本题的实现代码：</p>\n\n<h2>C语言源代码</h2>\n\n<pre>\n#include &lt;stdio.h&gt;\n#include &lt;stdlib.h&gt;\n\nint main ()\n{\n	int a, b,sum;\n	while (scanf (&quot;%d %d&quot;, &amp;a, &amp;b))\n	{\n		if (a == 0 &amp;&amp; b == 0)\n		{\n			break;\n		}\n		// ---- if ! (a == 0 &amp;&amp; b == 0), the program will not quit.\n		sum = a + b;\n		printf (&quot;%d\\n&quot;, sum);\n	}\n	return 0;\n}\n</pre>\n\n<h2>C++语言源代码</h2>\n\n<pre>\n#include &lt;iostream&gt;\nusing namespace std;\n\nint main ()\n{\n	int a, b;\n	while (cin &gt;&gt; a &gt;&gt; b)\n	{\n		if (a == 0 &amp;&amp; b == 0)\n		{\n			break;\n		}\n		// ---- if ! (a == 0 &amp;&amp; b == 0), the program will not quit.\n		int sum = a + b;\n		cout &lt;&lt; sum &lt;&lt; endl;\n	}\n	return 0;\n}\n</pre>\n\n<h2>Java语言源代码</h2>\n\n<pre>\nimport java.util.*;\n\npublic class Main\n{\n	public static void main (String[] args)\n	{\n		Scanner scanner = new Scanner(System.in);\n		int a, b;\n		a = scanner.nextInt();\n		b = scanner.nextInt();\n		while (!(a == 0 &amp;&amp; b == 0))\n		{\n			int sum;\n			sum = a + b;\n			System.out.println (sum);\n			a = scanner.nextInt();\n			b = scanner.nextInt();\n		}\n	}\n}\n</pre>\n\n<h2>Python 2 语言源代码</h2>\n\n<pre>\nwhile True:\n	a,b = map(int, raw_input().split())\n	if a + b == 0:\n		exit(0)\n	print a + b\n</pre>\n\n<h2>Python 3 语言源代码</h2>\n\n<pre>\nwhile True:\n	a,b = map(int, input().split())\n	if a + b == 0:\n		exit(0)\n	print (a + b)\n</pre>\n',
        '<p>有多组测试数据，每组测试数据输入两个数a和b(1&lt;=a,b&lt;=100). 当输入a=0且b=0时，表示输入结束，也就是说你不需要处理这组数据。</p>\n',
        '<p>对于每组测试数据输出一个数字表示a+b的值,并且每个输出占一行。</p>\n', '1 1 \n2 2 \n0 0\n', '2\n4\n', '0', '', 'ismdeep',
        '2013-12-10 20:31:55', 1, 128, 'N', 0, 0, 0, NULL, 'beginner,simple', '2018-05-17 14:20:55',
        '2021-06-03 06:47:04'),
       (1001, 'Problem A+B (II)', '<p>给出两个不大于100的正整数a和b，要你输出a+b的值。</p>',
        '<p>第一行有个T，表示有T组测试数据。接下来跟着T行，每行有2个整数表示每组测试数据的两个数a和b(1&lt;=a,b&lt;=100)</p>',
        '<p>对于每组测试数据输出一个数字表示a+b的值,并且每组输出占一行。</p>\r\n<p></p>\r\n<p></p>\r\n<p>下面将展示出几种不同编程语言下本题的实现代码：</p>\r\n<h2>C语言源代码</h2>\r\n<pre class=\"brush:c\">\r\n#include &lt;stdio.h&gt;\r\n#include &lt;stdlib.h&gt;\r\n\r\nint main ()\r\n{\r\n	int a, b, t, sum;\r\n	scanf (&quot;%d&quot;, &amp;t);\r\n	while (t--)\r\n	{\r\n		scanf (&quot;%d %d&quot;, &amp;a, &amp;b);\r\n		sum = a + b;\r\n		printf (&quot;%d\\n&quot;, sum);\r\n	}\r\n	return 0;\r\n}\r\n\r\n\r\n</pre>\r\n<h2>C++语言源代码</h2>\r\n<pre class=\"brush:cpp\">\r\n#include &lt;iostream&gt;\r\nusing namespace std;\r\n\r\nint main ()\r\n{\r\n	int a, b;\r\n	int t;\r\n	cin &gt;&gt; t;\r\n	while (t--)\r\n	{\r\n		cin &gt;&gt; a &gt;&gt; b;\r\n		int sum = a + b;\r\n		cout &lt;&lt; sum &lt;&lt; endl;\r\n	}\r\n	return 0;\r\n}\r\n\r\n\r\n</pre>\r\n<h2>Java语言源代码</h2>\r\n<pre class=\"brush:java\">\r\nimport java.util.*;\r\n\r\npublic class Main\r\n{\r\n	public static void main (String[] args)\r\n	{\r\n		Scanner scanner = new Scanner(System.in);\r\n		int a, b,t;\r\n		t = scanner.nextInt();\r\n		while (t &gt; 0)\r\n		{\r\n			t--;\r\n			a = scanner.nextInt();\r\n			b = scanner.nextInt();\r\n			int sum;\r\n			sum = a + b;\r\n			System.out.println (sum);\r\n		}\r\n	}\r\n}\r\n\r\n\r\n</pre>',
        '2\r\n1 1 \r\n2 2 \r\n', '2\r\n4\r\n', '0', '', 'ismdeep', '2015-12-03 09:42:20', 1, 128, 'N', 0, 0, 0, NULL,
        'beginner,simple', '2018-05-17 14:20:55', '2021-05-25 12:59:46');
/*!40000 ALTER TABLE `problem` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `problem_log`
--

DROP TABLE IF EXISTS `problem_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `problem_log`
(
    `id`            int                                                           NOT NULL AUTO_INCREMENT,
    `problem_id`    int                                                           NOT NULL,
    `title`         varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    `description`   text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `input`         text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `output`        text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `sample_input`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `sample_output` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `spj`           char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci      NOT NULL DEFAULT '0',
    `hint`          text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `source`        varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    `in_date`       datetime                                                               DEFAULT NULL,
    `time_limit`    int                                                           NOT NULL DEFAULT '0',
    `memory_limit`  int                                                           NOT NULL DEFAULT '0',
    `defunct`       char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci      NOT NULL DEFAULT 'N',
    `tags`          varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    `op_user_id`    varchar(48) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NOT NULL,
    `create_time`   timestamp                                                     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time`   timestamp                                                     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) COMMENT='问题操作日志';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `problem_log`
--

LOCK
TABLES `problem_log` WRITE;
/*!40000 ALTER TABLE `problem_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `problem_log` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `problem_tag`
--

DROP TABLE IF EXISTS `problem_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `problem_tag`
(
    `problem_id`  int                                                          NOT NULL,
    `tag_id`      varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `create_time` timestamp                                                    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time` timestamp                                                    NOT NULL DEFAULT CURRENT_TIMESTAMP
) COMMENT='题目标签';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `problem_tag`
--

LOCK
TABLES `problem_tag` WRITE;
/*!40000 ALTER TABLE `problem_tag` DISABLE KEYS */;
INSERT INTO `problem_tag`
VALUES (1001, 'beginner', '2019-12-14 12:56:29', '2019-12-14 12:56:29'),
       (1001, 'simple', '2019-12-14 12:56:29', '2019-12-14 12:56:29'),
       (1000, 'beginner', '2019-12-14 12:56:36', '2019-12-14 12:56:36'),
       (1000, 'simple', '2019-12-14 12:56:36', '2019-12-14 12:56:36');
/*!40000 ALTER TABLE `problem_tag` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `problem_tag_dict`
--

DROP TABLE IF EXISTS `problem_tag_dict`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `problem_tag_dict`
(
    `tag_id`      varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `tag_name_cn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `tag_name_en` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `cnt`         int                                                          NOT NULL DEFAULT '0',
    `create_time` timestamp                                                    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time` timestamp                                                    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`tag_id`)
) COMMENT='问题标签字典';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `problem_tag_dict`
--

LOCK
TABLES `problem_tag_dict` WRITE;
/*!40000 ALTER TABLE `problem_tag_dict` DISABLE KEYS */;
INSERT INTO `problem_tag_dict`
VALUES ('beginner', '入门题', 'Beginner', 2, '2019-12-14 12:51:02', '2020-08-10 13:13:14'),
       ('brute-force', '暴力题', 'Brute Force', 0, '2019-12-14 12:51:26', '2019-12-14 12:51:26'),
       ('combinatorics', '组合数学', 'Combinatorics', 0, '2019-12-14 12:51:26', '2019-12-14 13:38:08'),
       ('compilers', '编译原理', 'Compilers', 0, '2019-12-14 13:10:21', '2020-01-14 21:44:39'),
       ('computational-geo', '计算几何', 'Computational Geo', 0, '2019-12-14 13:05:34', '2020-06-02 15:03:05'),
       ('constructive-algo', '构造题', 'Construction', 0, '2019-12-14 12:51:38', '2021-03-26 05:17:30'),
       ('data-structure', '数据结构', 'Data Structure', 0, '2019-12-14 12:51:48', '2021-03-20 04:37:35'),
       ('dp', '动态规划', 'Dynamic Programming', 0, '2019-12-14 12:51:57', '2020-12-24 01:21:00'),
       ('graph', '图论', 'Grph', 0, '2019-12-14 12:53:30', '2019-12-14 12:53:30'),
       ('greedy', '贪心', 'Greedy', 0, '2019-12-14 12:53:17', '2019-12-14 13:39:34'),
       ('implementation', '实现', 'Implementation', 0, '2019-12-14 12:53:08', '2020-12-24 01:21:30'),
       ('jxust-c', '校赛', 'JXUST C', 0, '2019-12-15 12:28:03', '2021-03-02 12:24:15'),
       ('lang-basic', '语言基础', 'Language Basic', 0, '2019-12-14 12:53:00', '2020-08-10 13:12:23'),
       ('math', '数学题', 'Math', 0, '2019-12-14 12:52:49', '2019-12-15 12:34:29'),
       ('noip', 'NOIP', 'NOIP', 0, '2019-12-14 12:52:49', '2019-12-14 12:52:49'),
       ('number-theory', '数论', 'Number Theory', 0, '2019-12-14 12:52:36', '2020-12-24 01:19:59'),
       ('search', '搜索', 'Search', 0, '2019-12-14 12:52:25', '2019-12-15 12:46:07'),
       ('simple', '简单题', 'Simple', 2, '2019-12-14 12:51:26', '2020-12-24 01:21:50'),
       ('sorting', '排序', 'Sorting', 0, '2019-12-14 12:52:18', '2020-12-24 01:22:07'),
       ('string', '字符串', 'String', 0, '2019-12-14 12:52:08', '2020-08-10 13:13:07');
/*!40000 ALTER TABLE `problem_tag_dict` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `runtimeinfo`
--

DROP TABLE IF EXISTS `runtimeinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `runtimeinfo`
(
    `solution_id` int NOT NULL DEFAULT '0',
    `error`       text,
    `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`solution_id`)
) COMMENT='运行时错误信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `runtimeinfo`
--

LOCK
TABLES `runtimeinfo` WRITE;
/*!40000 ALTER TABLE `runtimeinfo` DISABLE KEYS */;
/*!40000 ALTER TABLE `runtimeinfo` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `sim`
--

DROP TABLE IF EXISTS `sim`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sim`
(
    `s_id`     int NOT NULL,
    `sim_s_id` int DEFAULT NULL,
    `sim`      int DEFAULT NULL,
    PRIMARY KEY (`s_id`),
    KEY        `Index_sim_id` (`sim_s_id`)
) COMMENT='代码查重';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sim`
--

LOCK
TABLES `sim` WRITE;
/*!40000 ALTER TABLE `sim` DISABLE KEYS */;
/*!40000 ALTER TABLE `sim` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `solution`
--

DROP TABLE IF EXISTS `solution`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `solution`
(
    `solution_id` int      NOT NULL AUTO_INCREMENT,
    `problem_id`  int      NOT NULL DEFAULT '0',
    `user_id`     char(48) NOT NULL,
    `time`        int      NOT NULL DEFAULT '0',
    `memory`      int      NOT NULL DEFAULT '0',
    `in_date`     datetime NOT NULL DEFAULT '0000-01-01 00:00:00',
    `result`      smallint NOT NULL DEFAULT '0',
    `language`    int unsigned NOT NULL DEFAULT '0',
    `ip`          char(15) NOT NULL,
    `contest_id`  int               DEFAULT NULL,
    `code_length` int      NOT NULL DEFAULT '0',
    `judgetime`   datetime          DEFAULT NULL,
    `judger`      varchar(255)      DEFAULT NULL,
    `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`solution_id`),
    KEY           `uid` (`user_id`),
    KEY           `pid` (`problem_id`),
    KEY           `res` (`result`),
    KEY           `cid` (`contest_id`),
    KEY           `psolutionid` (`solution_id`) USING BTREE
) COMMENT='提交';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solution`
--

LOCK
TABLES `solution` WRITE;
/*!40000 ALTER TABLE `solution` DISABLE KEYS */;
/*!40000 ALTER TABLE `solution` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `source_code`
--

DROP TABLE IF EXISTS `source_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `source_code`
(
    `solution_id` int  NOT NULL,
    `source`      text NOT NULL,
    `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`solution_id`)
) COMMENT='源代码';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `source_code`
--

LOCK
TABLES `source_code` WRITE;
/*!40000 ALTER TABLE `source_code` DISABLE KEYS */;
/*!40000 ALTER TABLE `source_code` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `ui_language`
--

DROP TABLE IF EXISTS `ui_language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ui_language`
(
    `id`          int         NOT NULL AUTO_INCREMENT,
    `user_id`     varchar(48) NOT NULL,
    `language`    varchar(10) NOT NULL,
    `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_id` (`user_id`)
) COMMENT='用户保存用户选择界面语言是中文还是English的记录表。';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ui_language`
--

LOCK
TABLES `ui_language` WRITE;
/*!40000 ALTER TABLE `ui_language` DISABLE KEYS */;
INSERT INTO `ui_language`
VALUES (1, 'ismdeep', 'cn', '2019-11-01 10:32:38', '2021-05-24 00:26:20');
/*!40000 ALTER TABLE `ui_language` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `upload`
--

DROP TABLE IF EXISTS `upload`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `upload`
(
    `id`          bigint                                                        NOT NULL AUTO_INCREMENT COMMENT '上传记录ID',
    `user_id`     varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NOT NULL COMMENT '用户ID',
    `oss_path`    varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Aliyun OSS 目录地址',
    `file_size`   bigint                                                        NOT NULL COMMENT '文件大小，单位B',
    `create_time` timestamp                                                     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time` timestamp                                                     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) COMMENT='上传文件';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `upload`
--

LOCK
TABLES `upload` WRITE;
/*!40000 ALTER TABLE `upload` DISABLE KEYS */;
/*!40000 ALTER TABLE `upload` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users`
(
    `user_id`        varchar(48)  NOT NULL DEFAULT '' COMMENT 'user_id',
    `email`          varchar(100)          DEFAULT NULL,
    `email_verified` tinyint      NOT NULL DEFAULT '0' COMMENT '邮箱是否验证，1是，0否，默认为0',
    `submit`         int                   DEFAULT '0',
    `solved`         int                   DEFAULT '0',
    `defunct`        char(1)      NOT NULL DEFAULT 'N',
    `ip`             varchar(20)  NOT NULL DEFAULT '',
    `accesstime`     datetime              DEFAULT NULL,
    `volume`         int          NOT NULL DEFAULT '1',
    `language`       int          NOT NULL DEFAULT '1',
    `password`       varchar(32)           DEFAULT NULL,
    `reg_time`       datetime              DEFAULT NULL,
    `nick`           varchar(100) NOT NULL DEFAULT '',
    `realname`       varchar(100) NOT NULL DEFAULT '',
    `school`         varchar(100) NOT NULL DEFAULT '',
    `academy`        varchar(100)          DEFAULT NULL,
    `class`          varchar(100)          DEFAULT NULL,
    `phone`          char(11)              DEFAULT NULL,
    `create_time`    timestamp NULL DEFAULT NULL,
    `update_time`    timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`user_id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK
TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users`
VALUES ('ismdeep', 'l.jiang.1024@gmail.com', 1, 0, 0, 'N', '127.0.0.1', '2013-11-29 22:57:33', 13, 1,
        '7OD5C+NYeqvE20kQpEzf3GKN2E44ZGY2', '2013-11-29 22:57:33', 'L. Jiang', '江木', '江西理工大学', '信息工程学院', '信研2018',
        '138xxxxxxxx', NULL, '2021-05-12 14:15:20');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK
TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-06-22  2:46:20
