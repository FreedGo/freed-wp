<?php

class editormd {
	//启用模式
	public function activate() {
		global $current_user;
		update_user_option( $current_user->ID, 'rich_editing', 'false', true );
	}

	//停用模式
	public function deactivate() {
		global $current_user;
		update_user_option( $current_user->ID, 'rich_editing', 'true', true );
	}

	// 提取jetpack模块
	function editormd_jetpack_markdown_posting_always_on() {
		global $wp_settings_fields;
		if ( isset( $wp_settings_fields['writing']['default'][ WPCom_Markdown::POST_OPTION ] ) ) {
			unset( $wp_settings_fields['writing']['default'][ WPCom_Markdown::POST_OPTION ] );
		}
	}

	// 载入插件语言
	function editormd_init_languages() {
		load_plugin_textdomain( 'editormd', false, dirname( plugin_basename( __FILE__ ) ) . '/Languages/' );
	}

	// 提取jetpack模块-->载入语言
	function editormd_jetpack_markdown_load_textdomain() {
		load_plugin_textdomain( 'jetpack', false, dirname( plugin_basename( __FILE__ ) ) . '/Jetpack/languages/' );
	}

	// 载入插件设置

	/**
	 * @param $actions
	 *
	 * @return array
	 */
	function jetpack_markdown_settings_link( $actions ) {
		return array_merge(
			array( 'settings' => sprintf( '<a href="%s">%s</a>', 'options-general.php?page=' . plugin_basename( __DIR__ . '/editormd_options.php' ), __( 'Settings', 'jetpack' ) ) ),
			$actions
		);
	}

	//加载编辑器相关配置
	public function load_editormd() {
		if ( get_current_screen()->base !== 'post' ) {
			return;
		}
		//获取数据库
		$options   = get_option( 'editormd_options' );
		$emoji_img = isset( $options['support_emoji_library'] ) && $options['support_emoji_library'] == '' ? WP_EDITORMD_PLUGIN_URL . '/Emojify.js/images/basic/' : $options['support_emoji_library'] . '/images/basic/';
		$katex     = isset( $options['support_latex_editormd_library'] ) && $options['support_latex_editormd_library'] == '' ? WP_EDITORMD_PLUGIN_URL . '/KaTeX/katex.min' : $options['support_latex_editormd_library'] . '/katex.min';
		?>
        <script type="text/javascript" defer="defer" charset="UTF-8">
            jQuery(document).ready(function ($) {
                // 初始化編輯器
                var EditorMD;
                $(function () {
                    EditorMD = editormd("wp-content-editor-container", {
                        width: "100%", //编辑器宽度
                        height: 640,    //编辑器高度
                        syncScrolling: <?php isset( $options['support_sync_scrolling'] ) && $options['support_sync_scrolling'] == 1 ? print( "false" ) : print( "true" ); ?>,   //即是否开启同步滚动预览
                        htmlDecode: <?php isset( $options['support_html_decode'] ) && $options['support_html_decode'] == 1 ? print( "true" ) : print( "false" ); ?>, //HTML标签解析
                        toolbarAutoFixed: true,   //工具栏是否自动固定
                        tocm: false,
                        tocContainer: <?php isset( $options['support_toc'] ) && $options['support_toc'] == 1 ? print( "''" ) : print( "false" ); ?>, //TOC
                        tocDropdown: false,
                        theme: "<?php isset( $options['theme_dark'] ) && $options['theme_dark'] == 1 ? print( "dark" ) : print( "default" ); ?>", //编辑器主题
                        previewTheme: "<?php isset( $options['theme_dark'] ) && $options['theme_dark'] == 1 ? print( "dark" ) : print( "default" ); ?>", //编辑器主题
                        editorTheme: "<?php isset( $options['theme_dark'] ) && $options['theme_dark'] == 1 ? print( "pastel-on-dark" ) : print( "default" ); ?>", //编辑器主题
                        emoji: <?php isset( $options['support_emoji'] ) && $options['support_emoji'] == 1 ? print( "true" ) : print( "false" ); ?>, //Emoji表情
                        tex: <?php isset( $options['support_latex'] ) && $options['support_latex'] == 1 ? print( "true" ) : print( "false" ) ?>, //LaTeX公式
                        atLink: false,//Github @Link
                        flowChart: <?php isset( $options['support_flowchart'] ) && $options['support_flowchart'] == 1 ? print( "true" ) : print( "false" ) ?>, //FlowChart流程图
                        sequenceDiagram : <?php isset( $options['support_sequence'] ) && $options['support_sequence'] == 1 ? print( "true" ) : print( "false" ) ?>,//SequenceDiagram时序图
                        taskList: <?php isset( $options['support_task_list'] ) && $options['support_task_list'] == 1 ? print( "true" ) : print( "false" ) ?>,//task lists
                        path: "<?php echo WP_EDITORMD_PLUGIN_URL ?>/Editor.md/lib/", //资源路径
                        toolbarIcons: function () {
                            // Or return editormd.toolbarModes[name]; // full, simple, mini
                            // Using "||" set icons align right.
                            return [
                                "undo", "redo", "|",
                                "bold", "del", "italic", "quote", "ucwords", "uppercase", "lowercase", "|",
                                "h1", "h2", "h3", "h4", "h5", "h6", "|",
                                "list-ul", "list-ol", "hr", "|",
                                "link", "reference-link", "image", "code", "preformatted-text", "code-block", "table", "datetime", "html-entities", "more",<?php isset( $options['support_emoji'] ) && $options['support_emoji'] == 1 ? print( "\"emoji\"," ) : print( "" ); ?> "|",
                                "goto-line", "watch", "preview", "fullscreen", "clear", "search", "|",
                                "help", "info"
                            ];
                        }, //自定义标题栏
                        toolbarIconsClass: {
                            more: "fa-arrows-h" //指定一个FontAawsome的图标类
                        },
                        // 自定义工具栏按钮的事件处理
                        toolbarHandlers: {
                            /**
                             * @param {Object}      cm         CodeMirror对象
                             * @param {Object}      icon       图标按钮jQuery元素对象
                             * @param {Object}      cursor     CodeMirror的光标对象，可获取光标所在行和位置
                             * @param {String}      selection  编辑器选中的文本
                             */
                            more: function (cm, icon, cursor, selection) {
                                cm.replaceSelection("<!--more-->");
                            }
                        },
                        lang: {
                            toolbar: {
                                more: "摘要分隔符"
                            }
                        },
                        //强制全屏
                        onfullscreen: function () {
                            window.document.getElementById("wp-content-editor-container").style.position = "fixed";
                            window.document.getElementById("wp-content-editor-container").style.zIndex = "99999";
                        },
                        //退出全屏返回原来的样式
                        onfullscreenExit: function () {
                            window.document.getElementById("wp-content-editor-container").style.position = "relative";
                            window.document.getElementById("wp-content-editor-container").style.zIndex = "auto";
                        }
                    });
                });
                //隐藏原来编辑器工具栏
                document.getElementById("ed_toolbar").style.display = "none";
                //WP Media module支持
                var original_wp_media_editor_insert = wp.media.editor.insert;
                wp.media.editor.insert = function (html) {
                    //console.log(html);
                    //创建新的DOM
                    var htmlDom = document.createElement("div");
                    htmlDom.style.display = "none";
                    htmlDom.id = "htmlDom";
                    htmlDom.innerHTML = html;
                    document.body.appendChild(htmlDom);
                    //获取src属性
                    var htmlSrc = window.document.getElementsByClassName("alignnone")[0].src;
                    var htmlAlt = window.document.getElementsByClassName("alignnone")[0].alt;
                    //插入Markdown
                    var markdownSrc = '![' + htmlAlt + '](' + htmlSrc + ')';
                    original_wp_media_editor_insert(markdownSrc);
                    EditorMD.insertValue(markdownSrc);
                    //移除dom
                    document.getElementById("htmlDom").remove();
                };
				<?php
				/*Emoji配置脚本*/
				if ( isset( $options['support_emoji'] ) && $options['support_emoji'] == 1 ) {
					echo "
                //Emoji表情自定义服务器地址
                editormd.emoji = {
                    path: \"$emoji_img\",
                    ext: \".png\"
                };";
				}
				/*LaTeX公式配置脚本*/
				if ( isset( $options['support_latex'] ) && $options['support_latex'] == 1 ) {
					echo "
				//KaTeX科学公式加载库地址
                editormd.katexURL = {
                    css : \"$katex\",
                    js  : \"$katex\"
                };";
				}
				/*图像粘贴配置脚本*/
				if ( isset( $options['support_imagepaste'] ) && $options['support_imagepaste'] == 1 ) {
					echo "
                    //监听图像粘贴事件
                    $(\"#wp-content-editor-container\").on('paste', function (event) {
                        event = event.originalEvent;
                        var cbd = window.clipboardData || event.clipboardData; //兼容ie||chrome
                        var ua = window.navigator.userAgent;
                        if (!(event.clipboardData && event.clipboardData.items)) {
                            return;
                        }
                        if (cbd.items && cbd.items.length === 2 && cbd.items[0].kind === \"string\" && cbd.items[1].kind === \"file\" &&
                            cbd.types && cbd.types.length === 2 && cbd.types[0] === \"text/plain\" && cbd.types[1] === \"Files\" &&
                            ua.match(/Macintosh/i) && Number(ua.match(/Chrome\/(\d{2})/i)[1]) < 49) {
                            return;
                        }
                        var itemLength = cbd.items.length;
                        if (itemLength === 0) {
                            return;
                        }
                        if (itemLength === 1 && cbd.items[0].kind === 'string') {
                            return;
                        }
                        if ((itemLength === 1 && cbd.items[0].kind === 'file')) {
                            var item = cbd.items[0];
                            var blob = item.getAsFile();
                            if (blob.size === 0) {
                                return;
                            }
                            //封装FileReader对象
                            function readBlobAsDataURL(blob, callback) {
                                var reader = new FileReader();
                                reader.onload = function (e) {
                                    callback(e.target.result);
                                };
                                reader.readAsDataURL(blob);
                            }
                            //传参
                            readBlobAsDataURL(blob, function (dataurl) {
                                var uploadingText = '![图片上传中...]';
                                var uploadFailText = '![图片上传失败]';
                                var data = {
                                    action: \"imagepaste_action\",
                                    dataurl: dataurl
                                };
                                EditorMD.insertValue(uploadingText);
                                $.ajax({
                                    url: ajaxurl,
                                    type: \"post\",
                                    data: data,
                                    success: function (request) {
                                        var obj = eval(\"(\" + request + \")\");
                                        if (obj.error) {
                                            EditorMD.setValue(EditorMD.getValue().replace(uploadingText, uploadFailText));
                                        } else {
                                            EditorMD.setValue(EditorMD.getValue().replace(uploadingText, '![](' + obj.url + ')'));
                                        }
                                    }
                                });
                            });
                        }
                    });
                    if (localStorage.getItem(\"wp_editormd\") !== 'true') {
                        alert(\"图像功能暂未完善，请慎重使用！\");
                        localStorage.setItem(\"wp_editormd\",\"true\");
                    };
                    <!--End-->
                    ";
				}
				?>
            });
        </script>
		<?php
	}

	//保存设置数据
	public function user_personalopts_update() {
		global $current_user;
		update_user_option( $current_user->ID, 'rich_editing', 'false', true );
	}

	//载入JavaScript脚本
	public function add_admin_js() {
		//只在需要有文章编辑器才能加载以下文件
		if ( get_current_screen()->base !== 'post' ) {
			return;
		}
		wp_deregister_script( 'media-upload' );//禁止加载多媒体脚本(减少对编辑器的干扰);
		wp_enqueue_script( 'jquery_js', WP_EDITORMD_PLUGIN_URL . '/jQuery/jquery.min.js', array(), WP_EDITORMD_PLUGIN_VERSION, true );
		wp_enqueue_script( 'editormd_js', WP_EDITORMD_PLUGIN_URL . '/Editor.md/js/editormd.min.js', array(), WP_EDITORMD_PLUGIN_VERSION, true );
		wp_enqueue_script( 'xssjs', WP_EDITORMD_PLUGIN_URL . '/XSS/xss.min.js', array(), WP_EDITORMD_PLUGIN_VERSION, true );

		//载入国际化语言资源文件
		$lang = get_bloginfo( 'language' );
		switch ( $lang ) {
			case 'zh-TW':
				wp_enqueue_script( 'lang_tw', WP_EDITORMD_PLUGIN_URL . '/Editor.md/lib/languages/zh-tw.js', array(), WP_EDITORMD_PLUGIN_VERSION, true );//载入台湾语言资源库
				break;
			case 'zh-HK':
				wp_enqueue_script( 'lang_hk', WP_EDITORMD_PLUGIN_URL . '/Editor.md/lib/languages/zh-hk.js', array(), WP_EDITORMD_PLUGIN_VERSION, true );//载入港澳语言资源库
				break;
			case 'zh-CN':
				break;
			case 'en-US':
				wp_enqueue_script( 'lang_us', WP_EDITORMD_PLUGIN_URL . '/Editor.md/lib/languages/en.js', array(), WP_EDITORMD_PLUGIN_VERSION, true );//载入美国英语语言资源库
				break;
			default:
				wp_enqueue_script( 'lang_us', WP_EDITORMD_PLUGIN_URL . '/Editor.md/lib/languages/en.js', array(), WP_EDITORMD_PLUGIN_VERSION, true );//默认载入美国英语语言资源库
				break;
		}
	}

	//载入Style样式文件
	public function add_admin_style() {
		//只在需要有文章编辑器才能加载以下文件
		if ( get_current_screen()->base !== 'post' ) {
			return;
		}
		wp_deregister_style( 'media-upload' );
		wp_enqueue_style( 'editormd_css', WP_EDITORMD_PLUGIN_URL . '/Editor.md/css/editormd.min.css', array(), WP_EDITORMD_PLUGIN_VERSION, 'all' );
	}

	public function add_admin_head() {
		?>
        <style type="text/css" rel="stylesheet">
            .editormd_wrap input#submit {border: none}
            .markdown-body img.emoji {height: 24px !important;width: 24px !important}
            .markdown-body h2 {font-size: 1.75em !important;line-height: 1.225 !important;padding: 0 0 .3em 0 !important}
            .markdown-body.editormd-preview-container ul {list-style: initial}
            .markdown-body.editormd-preview-container ol {margin-left: 0 !important}
            .wrap a:active, .wrap a:hover, .wrap a:link, .wrap a:visited {text-decoration: none}
        </style>
		<?php
	}

	public function latex_enqueue_scripts() {
		$options   = get_option( 'editormd_options' );
		$katex_css = isset( $options['support_latex_editormd_library'] ) && $options['support_latex_editormd_library'] == '' ? WP_EDITORMD_PLUGIN_URL . '/katex.min.css' : $options['support_latex_editormd_library'] . '/katex.min.css';
		$katex_js  = isset( $options['support_latex_editormd_library'] ) && $options['support_latex_editormd_library'] == '' ? WP_EDITORMD_PLUGIN_URL . '/katex.min.js' : $options['support_latex_editormd_library'] . '/katex.min.js';
		wp_enqueue_style( 'katex_css', $katex_css, array(), WP_EDITORMD_PLUGIN_VERSION, 'all' );
		wp_enqueue_script( 'katex_js', $katex_js, array(), WP_EDITORMD_PLUGIN_VERSION, false );
	}

	public function flowchart_enqueue_scripts() {
		$options   = get_option( 'editormd_options' );
		$raphael_js = isset( $options['support_raphael_library'] ) && $options['support_raphael_library'] == '' ? WP_EDITORMD_PLUGIN_URL . '/raphael.min.js' : $options['support_raphael_library'] . '/raphael.min.js';
		$jquery_js = isset( $options['support_jquery_library'] ) && $options['support_jquery_library'] == '' ? WP_EDITORMD_PLUGIN_URL . '/jquery.min.js' : $options['support_jquery_library'] . '/jquery.min.js';
		$flowchart_js  = isset( $options['support_flowchart_library'] ) && $options['support_flowchart_library'] == '' ? WP_EDITORMD_PLUGIN_URL . '/flowchart.min.js' : $options['support_flowchart_library'] . '/flowchart.min.js';
		$jqueryflow_js  = isset( $options['support_jquery_flowchart_library'] ) && $options['support_jquery_flowchart_library'] == '' ? WP_EDITORMD_PLUGIN_URL . '/jquery.flowchart.min.js' : $options['support_jquery_flowchart_library'] . '/jquery.flowchart.min.js';
		wp_enqueue_script( 'raphaeljs', $raphael_js, array(), WP_EDITORMD_PLUGIN_VERSION, false );
		wp_enqueue_script( 'jquery-js', $jquery_js, array(), WP_EDITORMD_PLUGIN_VERSION, false );
		wp_enqueue_script( 'flowchartjs', $flowchart_js, array(), WP_EDITORMD_PLUGIN_VERSION, false );
		wp_enqueue_script( 'jqueryflowjs', $jqueryflow_js, array(), WP_EDITORMD_PLUGIN_VERSION, false );
	}

	public function sequence_enqueue_scripts() {
		$options   = get_option( 'editormd_options' );
		$raphael_js = isset( $options['support_raphael_library'] ) && $options['support_raphael_library'] == '' ? WP_EDITORMD_PLUGIN_URL . '/raphael.min.js' : $options['support_raphael_library'] . '/raphael.min.js';
		$jquery_js = isset( $options['support_jquery_library'] ) && $options['support_jquery_library'] == '' ? WP_EDITORMD_PLUGIN_URL . '/jquery.min.js' : $options['support_jquery_library'] . '/jquery.min.js';
		$sequence_js  = isset( $options['support_sequence_library'] ) && $options['support_sequence_library'] == '' ? WP_EDITORMD_PLUGIN_URL . '/sequence-diagram.min.js' : $options['support_sequence_library'] . '/sequence-diagram.min.js';
		$underscore_js  = isset( $options['support_underscore_library'] ) && $options['support_underscore_library'] == '' ? WP_EDITORMD_PLUGIN_URL . '/underscore.min.js' : $options['support_underscore_library'] . '/underscore.min.js';
		wp_enqueue_script( 'underscore_js', $underscore_js, array(), WP_EDITORMD_PLUGIN_VERSION, false );
		wp_enqueue_script( 'raphaeljs', $raphael_js, array(), WP_EDITORMD_PLUGIN_VERSION, false );
		wp_enqueue_script( 'jquery-js', $jquery_js, array(), WP_EDITORMD_PLUGIN_VERSION, false );
		wp_enqueue_script( 'sequence_js', $sequence_js, array(), WP_EDITORMD_PLUGIN_VERSION, false );
	}

	//前端Emoji表情
	public function emoji_enqueue_scripts() {
		//获取数据库
		$options     = get_option( 'editormd_options' );
		$emojify_css = isset( $options['support_emoji_library'] ) && $options['support_emoji_library'] == '' ? WP_EDITORMD_PLUGIN_URL . '/Emojify.js/css/basic/emojify.min.css' : $options['support_emoji_library'] . '/css/basic/emojify.min.css';
		$emojify_js  = isset( $options['support_emoji_library'] ) && $options['support_emoji_library'] == '' ? WP_EDITORMD_PLUGIN_URL . '/Emojify.js/js/emojify.min.js' : $options['support_emoji_library'] . '/js/emojify.min.js';
		wp_enqueue_style( 'emojify_css', $emojify_css, array(), WP_EDITORMD_PLUGIN_VERSION, 'all' );
		wp_enqueue_script( 'emojify_js', $emojify_js, array(), WP_EDITORMD_PLUGIN_VERSION, true );
	}

	public function emoji_enqueue_footer_js() {
		$options   = get_option( 'editormd_options' );
		$emoji_img = isset( $options['support_emoji_library'] ) && $options['support_emoji_library'] == '' ? WP_EDITORMD_PLUGIN_URL . '/Emojify.js/images/basic' : $options['support_emoji_library'] . '/images/basic';
		?>
        <script type="text/javascript" charset="UTF-8">
            window.onload = function () {
                emojify.setConfig({
                    img_dir: "<?php echo $emoji_img ?>",//前端emoji资源地址
                    blacklist: {
                        'ids': [],
                        'classes': ['no-emojify'],
                        'elements': ['^script$', '^textarea$', '^pre$', '^code$']
                    }
                });
                emojify.run();
            }
        </script>
		<?php
	}
}

$editormd = new editormd();
