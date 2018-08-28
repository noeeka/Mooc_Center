<style lang="less">
    @import '../../styles/common.less';
    @import './chapter-edit.less';
</style>

<template>
    <div>
        <Row>
            <Card id="header">
                <div slot="title">
                    <p style="width: auto;">
                        <Icon type="pound"></Icon>
                        章节管理
                    </p>
                    <p style="width: auto; float: right;height:25px;">
                        <Button type="primary" size="small" @click="handleBack">返回课程编辑</Button>
                    </p>
                    <div style="clear: both"></div>
                </div>
            </Card>
        </Row>
        <Row style="background-color: #fff">
            <Col span="6">
                <Card>
                    <p slot="title">
                        <Icon type="android-menu"></Icon>
                        章节目录
                    </p>
                    <Tree :data="data"></Tree>
                </Card>
            </Col>
            <Col span="18">
                <Card>
                    <p slot="title">
                        <Icon type="android-create"></Icon>
                        课时详情
                    </p>
                    <Form>
                        <FormItem label="课时名称:" :label-width="100">
                            <Col span="19"><Input v-model="section.section_title" disabled
                                                  @on-change="handleDataChange"/></Col>
                        </FormItem>
                        <FormItem label="视频库视频:" :label-width="100" id="video_lib" >
                            <Col span="18"><Input v-model="section.video_main"  id="video_main_url" value=""/></Col>
                            <Col span="4">
                                <Button @click="modal1 = true">视频库视频</Button>
                                <Modal
                                        v-model="modal1"
                                        title="选择视频"
                                        @on-ok="ok"
                                        @on-cancel="cancel">
                                    <RadioGroup v-model="video_url" @on-change="changeUrl" vertical>
                                        <Radio v-for="(name,id) in videoList" :key="id" :value="id" :label='id'>{{name}}</Radio>
                                    </RadioGroup>
                                </Modal>
                            </Col>
                        </FormItem>
                        <FormItem label="视频时长:" :label-width="100">
                            <Col span="19"><Input v-model="section.video_time" id="vTime"/></Col>
                        </FormItem>
                        <div style="margin-top:40px;text-align: center">
                            <Button type="text" @click="getSection(section.id)">重置</Button>
                            <Button style="margin-left: 20px;" type="primary" @click="saveData">保存</Button>
                        </div>
                    </Form>
                </Card>
            </Col>
        </Row>
    </div>
</template>

<script>
    // import tinymce from 'tinymce';
    import jq from 'jquery';
    import Cookies from 'js-cookie';

    export default {
        name: 'chapter_edit',
        data: function () {
            return {
                // 路由获取的课程id
                id: 0,
                // 新增章节弹窗数据
                chapter_title: '',
                // 新增课时弹窗数据
                section_title: '',
                // 重命名弹窗数据
                newName: '',
                // 重命名弹窗数据
                center_id: 0,
                modal1: false,
                video_id: 0,
                up_way: 0,
                // 当前活动的课时信息
                section: {
                    id: 0,
                    chapter_id: '',
                    section_title: '',
                    is_save: 1,
                    video_main: '',
                    video_backup: '',
                    video_time: ''
                },
                upload_data: {},
                videoList: {},
                vList: [],
                video_url: '',
                video_time: 0,
                // 章节目录根节点构建
                data: [
                    {
                        title: '课程',
                        expand: true,
                        render: (h, {root, node, data}) => {
                            let that = this;
                            return h('span', {
                                style: {
                                    display: 'inline-block',
                                    width: '100%'
                                }
                            }, [
                                h('span', [
                                    h('Icon', {
                                        props: {
                                            type: 'merge'
                                        },
                                        style: {
                                            marginRight: '8px'
                                        }
                                    }),
                                    h('span', data.title)
                                ]),
                                h('span', {
                                    style: {
                                        display: 'inline-block',
                                        float: 'right',
                                        marginRight: '32px'
                                    }
                                }, [
                                    h('Button', {
                                        props: Object.assign({}, this.buttonProps, {
                                            icon: 'ios-plus-empty',
                                            type: 'primary'
                                        }),
                                        on: {
                                            click: () => {
                                                that.addChapter(data);
                                            }
                                        },
                                        attrs: {
                                            title: '添加章节'
                                        }
                                    })
                                ])
                            ]);
                        },
                        children: []
                    }
                ],
                buttonProps: {
                    type: 'ghost',
                    size: 'small'
                }
            };
        },
        methods: {
            // handleFormatError: function () {
            //     this.$Message.error('文件格式错误');
            // },
            // handleSizeError: function () {
            //     this.$Message.error('文件超出大小限制');
            // },
            // handleBeforeUpload: function () {
            //     let that = this;
            //     this.request({
            //         api: '/v1/upload/upload',
            //         user_type: 3
            //     }, function (response) {
            //         if (response.status === 1) {
            //             that.upload_data = response.data;
            //         }
            //     }, false);
            //     return new Promise((resolve) => {
            //         this.$nextTick(function () {
            //             resolve(true);
            //         });
            //     });
            // },
            // handleBack: function () {
            //     this.$router.replace({name: 'course_edit', params: {id: this.id}});
            // },
            // handleProcess: function () {
            //     jq('#uploadbtn').text('上传中....');
            // },
            // handleSuccess1: function (response) {
            //     // 上传监听
            //     if (response.status === 1) {
            //         jq('#uploadbtn').text('上传成功 ');
            //         this.section.video_main = response.data.file_url;
            //         this.section.video_time = response.data.total_time;
            //     } else {
            //         this.$Message.error(response.msg);
            //     }
            // },
            // handleDataChange: function () {
            //     // 监听课时数据是否被修改
            //     this.section.is_save = 0;
            // },
            getVideo: function () {
                let that = this;
                this.request(
                    {
                        api: '/v1/video/getVideoList',
                        center_id: this.center_id
                    },
                    function (response) {
                        if (response.status === 1) {
                            that.vList = response.data;
                            that.videoList = that.array_map(response.data, 'id', 'title');
                        } else {
                            this.$Message.error('获取视频信息失败');
                        }
                    });
            },
            changeUrl: function (id) {
                for (let i = 0; i < this.vList.length; i++) {
                    if (this.vList[i].id.toString() === id) {
                        this.video_url = this.vList[i].url;
                        this.video_time = this.vList[i].video_time;
                    }
                }
            },
            ok () {
                this.section.video_main = this.video_url;
                this.section.video_time = this.video_time;
            },
            cancel () {
                window.close();
            },
            saveData: function () {
                // 保存数据
                let that = this;
                this.editSection(this.section, function (response) {
                    if (response.status === 1) {
                        that.section.is_save = 1;
                        that.$Message.success('保存成功');
                    } else {
                        that.$Message.error(response.msg);
                    }
                });
            },
            editSection: function (param, callback) {
                // 编辑课时
                param['api'] = '/v1/course/edit_section';
                param['user_type'] = 3;
                this.request(param, callback);
            },
            getCatalog: function () {
                // 加载章节目录
                let that = this;
                this.request({
                    api: '/v1/course/catalog',
                    id: this.id
                }, function (response) {
                    let data = response.data;
                    for (let i in data) {
                        data[i].render = function (h, {root, node, data}) {
                            return that.renderContent(h, {root, node, data}, 1);
                        };
                        data[i].level = 1;
                        for (let j in data[i].children) {
                            data[i]['children'][j].render = function (h, {root, node, data}) {
                                return that.renderContent(h, {root, node, data}, 2);
                            };
                            data[i]['children'][j].level = 2;
                        }
                    }
                    that.data[0]['children'] = data;
                });
            },
            getSection: function (id) {
                // 获取课时，并设置焦点
                let that = this;
                this.request({
                    api: '/v1/course/read_section',
                    id: id,
                    user_type: 3
                }, function (response) {
                    if (response.status) {
                        that.section = {
                            is_save: 1,
                            id: response.data.id,
                            chapter_id: response.data.chapter_id,
                            section_title: response.data.section_title,
                            video_main: response.data.video_main,
                            video_time: response.data.video_time
                        };
                    } else {
                        that.$Message.error('获取课时信息失败');
                    }
                });
            },
            doAddChapter: function (title, callback) {
                // 添加章节
                this.request({
                    api: '/v1/course/add_chapter',
                    id: this.id,
                    chapter_title: title,
                    user_type: 3
                }, callback);
            },
            doAddSection: function (chapterId, title, callback) {
                // 添加课时
                this.request({
                    api: '/v1/course/add_section',
                    id: this.id,
                    chapter_id: chapterId,
                    section_title: title,
                    user_type: 3
                }, callback);
            },
            del: function (datas, callback) {
                // 删除章节 实际操作
                let chapterId = 0;
                let sectionIds = [];
                if (datas.level === 1) {
                    chapterId = datas.id;
                    if (datas.children !== undefined) {
                        sectionIds = datas.children.map(function (v) {
                            return v.id;
                        });
                    }
                    this.request({
                        api: '/v1/course/del_chapter',
                        id: this.id,
                        chapter_id: chapterId,
                        user_type: 3
                    }, callback);
                    this.request({
                        api: '/v1/course/del_section',
                        ids: sectionIds,
                        chapter_id: chapterId,
                        user_type: 3
                    });
                } else {
                    sectionIds = [datas.id];
                    this.request({
                        api: '/v1/course/del_section',
                        ids: sectionIds,
                        chapter_id: chapterId,
                        user_type: 3
                    }, callback);
                }
            },
            addChapter: function (data) {
                // 添加章节
                let that = this;
                this.$Modal.confirm({
                    title: '填写章节名称',
                    render: function (h) {
                        return h('Input', {
                            props: {
                                placeholder: '请输入章节名称',
                                autofocus: true,
                                value: that.chapter_title
                            },
                            style: {
                                marginTop: '20px'
                            },
                            on: {
                                input: function (val) {
                                    that.chapter_title = val;
                                }
                            }
                        });
                    },
                    onOk: function () {
                        if (that.chapter_title === '') {
                            that.$Message.error('章节名称必须');
                        } else {
                            that.doAddChapter(that.chapter_title, function (response) {
                                if (response.status === 1) {
                                    that.append(response.data.id, that.chapter_title, data, 1);
                                    that.chapter_title = '';
                                } else {
                                    that.$Message.error(response.msg);
                                }
                            });
                        }
                    }
                });
            },
            addSection: function (data) {
                // 添加课时
                let that = this;
                this.$Modal.confirm({
                    title: '填写课时名称',
                    render: function (h) {
                        return h('Input', {
                            props: {
                                placeholder: '请输入课时名称',
                                autofocus: true,
                                value: that.section_title
                            },
                            style: {
                                marginTop: '20px'
                            },
                            on: {
                                input: function (val) {
                                    that.section_title = val;
                                }
                            }
                        });
                    },
                    onOk: function () {
                        if (that.section_title === '') {
                            that.$Message.error('课时名称必须');
                        } else {
                            that.doAddSection(data.id, that.section_title, function (response) {
                                if (response.status === 1) {
                                    that.append(response.data.id, that.section_title, data, 2);
                                    that.section_title = '';
                                    that.getSection(response.data.id);
                                } else {
                                    that.$Message.error(response.msg);
                                }
                            });
                        }
                    }
                });
            },
            rename: function (data) {
                // 重命名
                let that = this;
                this.$Modal.confirm({
                    title: '重命名',
                    render: function (h) {
                        return h('Input', {
                            props: {
                                placeholder: '请输入课时名称',
                                autofocus: true,
                                value: that.section_title
                            },
                            style: {
                                marginTop: '20px'
                            },
                            on: {
                                input: function (val) {
                                    that.newName = val;
                                }
                            }
                        });
                    },
                    onOk: function () {
                        if (that.newName === '') {
                            that.$Message.error('请填写名称');
                        } else {
                            if (data.level === 1) {
                                that.request({
                                    api: '/v1/course/edit_chapter',
                                    user_type: 3,
                                    chapter_title: that.newName,
                                    chapter_id: data.id
                                }, function (response) {
                                    if (response.status === 1) {
                                        that.$Message.success('重命名成功');
                                        that.getCatalog();
                                    } else {
                                        that.$Message.error(response.msg);
                                    }
                                });
                            } else {
                                that.editSection({id: data.id, section_title: that.newName}, function (response) {
                                    if (response.status === 1) {
                                        that.$Message.success('重命名成功');
                                        that.getCatalog();
                                    } else {
                                        that.$Message.error(response.msg);
                                    }
                                });
                            }
                        }
                    }
                });
            },
            renderContent (h, {root, node, data}, level) {
                // 构建课时或章节的删除，添加，重命名
                let that = this;
                let action = [
                    h('Button', {
                        props: Object.assign({}, this.buttonProps, {
                            icon: 'ios-plus-empty',
                            type: 'primary'
                        }),
                        style: {
                            marginRight: '8px'
                        },
                        attrs: {
                            title: '添加课时'
                        },
                        on: {
                            click: () => {
                                if (that.section.is_save === 1) {
                                    that.addSection(data);
                                } else {
                                    that.$Message.error('请先保存当前课时信息，再操作');
                                }
                            }
                        }
                    }),
                    h('Button', {
                        props: Object.assign({}, this.buttonProps, {
                            icon: 'edit',
                            type: 'primary',
                            size: 'small'
                        }),
                        style: {
                            marginRight: '8px',
                            width: '22px',
                            textIndent: '-1px'
                        },
                        attrs: {
                            title: '重命名'
                        },
                        on: {
                            click: () => {
                                if (that.section.is_save === 0 && that.section.id !== data.id) {
                                    that.$Message.error('请先保存当前课时信息，再操作');
                                } else {
                                    that.rename(data);
                                }
                            }
                        }
                    }),
                    h('Button', {
                        props: Object.assign({}, this.buttonProps, {
                            icon: 'ios-minus-empty',
                            type: 'error'
                        }),
                        attrs: {
                            title: '删除'
                        },
                        on: {
                            click: () => {
                                this.remove(root, node, data);
                            }
                        }
                    })];
                let icon = 'ios-folder-outline';
                let cursor = 'default';
                if (level !== 1) {
                    action.splice(0, 1);
                    icon = 'ios-paper-outline';
                    cursor = 'pointer';
                }
                return h('span',
                    {
                        style: {
                            display: 'inline-block',
                            width: '100%'
                        }
                    },
                    [
                        h('span', {
                            style: {
                                padding: '3px',
                                borderRadius: '3px',
                                cursor: cursor
                            },
                            attrs: {
                                class: 'section_title'
                            },
                            on: {
                                click: function (e) {
                                    if (level === 2) {
                                        if (that.section.is_save === 0 && that.section.id !== data.id) {
                                            that.$Message.error('请先保存当前课时信息，再操作');
                                        } else {
                                            that.getSection(data.id);
                                            jq('.section_title').removeClass('section-active');
                                            if (jq(e.target).hasClass('section_title')) {
                                                jq(e.target).addClass('section-active');
                                            } else {
                                                jq(e.target).parent().addClass('section-active');
                                            }
                                        }
                                    }
                                }
                            }
                        },
                        [
                            h('Icon', {
                                props: {
                                    type: icon
                                },
                                style: {
                                    marginRight: '8px'
                                }
                            }),
                            h('span', data.title)
                        ]),
                        h('span', {
                            style: {
                                display: 'inline-block',
                                float: 'right',
                                marginRight: '32px'
                            }
                        }, action)
                    ]);
            },
            append (id, title, data, level) {
                // 添加课时或章节
                const children = data.children || [];
                let that = this;
                children.push({
                    title: title,
                    id: id,
                    level: level,
                    expand: true,
                    render: function (h, {root, node, data}) {
                        return that.renderContent(h, {root, node, data}, level);
                    }
                });
                this.$set(data, 'children', children);
            },
            remove (root, node, data) {
                // 删除章节
                const parentKey = root.find(el => el === node).parent;
                const parent = root.find(el => el.nodeKey === parentKey).node;
                const index = parent.children.indexOf(data);
                let that = this;
                this.del(parent.children[index], function (response) {
                    if (response.status === 1) {
                        parent.children.splice(index, 1);
                    } else {
                        that.$Message.error(response.msg);
                    }
                });
            }
        },
        mounted () {
            // 初始化
            this.center_id = Cookies.get('center_id');
            this.id = this.$route.params.course_id;
            this.getCatalog();
            this.getVideo();
            // this.upload_token = this.getUploadData();
        }
    };
</script>
