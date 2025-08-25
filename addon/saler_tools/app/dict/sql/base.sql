CREATE TABLE `saler_tools_goods_brand` (
                                    `brand_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '品牌ID',
                                    `site_id` int(11) NOT NULL DEFAULT '0' COMMENT '站点id',
                                    `brand_name` varchar(100) NOT NULL DEFAULT '' COMMENT '品牌名称',
                                    `logo` varchar(255) NOT NULL DEFAULT '' COMMENT '品牌logo',
                                    `desc` text NOT NULL COMMENT '品牌介绍',
                                    `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
                                    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
                                    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
                                    `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
                                    PRIMARY KEY (`brand_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='商品品牌表';



CREATE TABLE `saler_tools_goods_category` (
                                       `category_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '商品分类id',
                                       `site_id` int(11) NOT NULL DEFAULT '0' COMMENT '站点id',
                                       `category_name` varchar(255) NOT NULL DEFAULT '' COMMENT '分类名称',
                                       `image` varchar(255) NOT NULL DEFAULT '' COMMENT '分类图片',
                                       `level` int(11) NOT NULL DEFAULT '0' COMMENT '层级',
                                       `pid` int(11) NOT NULL DEFAULT '0' COMMENT '上级分类id',
                                       `category_full_name` varchar(255) NOT NULL DEFAULT '' COMMENT '组装分类名称',
                                       `is_show` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否显示（1：显示，0：不显示）',
                                       `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序号',
                                       `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
                                       `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
                                       PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='商品分类表';



