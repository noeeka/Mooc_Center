<style lang="less">
    @import '../../styles/common.less';
    @import './course-add.less';
</style>

<template>
    <div>
        <Row>
            <Col span="15">
                <Card>
                    <Form ref="courseForm" class="margin-top-20" :label-width="80">
                        <FormItem class="margin-top-20" label="来源:">
                            <Select v-model="center_id" disabled  id="center_id" style="width: 300px" @on-change="handleChange">
                                <Option v-for="(name, id) in center" :value="id" :key="id">{{ name }}</Option>
                            </Select>
                            <label for="type_id">分类:</label>
                            <Cascader :data="type" disabled v-model="type_id" style="width: 150px;display: inline-block" id="type_id"></Cascader>
                        </FormItem>
                        <FormItem class="margin-top-20" label="标题:">
                            <Input v-model="course_title" disabled icon="android-list"/>
                        </FormItem>
                        <FormItem class="margin-top-20" label="封面图:">
                            <img v-if="cover_img" :src="cover_img" style="width: 120px;cursor:pointer;" alt="">
                            <img v-else src="../../images/image.png" style="width: 120px;cursor:pointer;" alt="">
                        </FormItem>
                        <FormItem v-model='status' class="margin-top-20" label="状态:">
                            <RadioGroup v-model="status">
                                <Radio label="1" disabled>显示</Radio>
                                <Radio label="0" disabled>隐藏</Radio>
                            </RadioGroup>
                        </FormItem>
                        <FormItem class="margin-top-20" label="简介:">
                            <textarea name="content" disabled v-model="content" id="content" cols="180" rows="5">
                            </textarea>
                        </FormItem>
                        <FormItem class="margin-top-20" label="上传视频:">
                            <Button type="primary" disabled @click="handleUploadVideo"><Icon type="ios-film"></Icon>&nbsp;上传视频</Button>
                        </FormItem>
                        <FormItem class="margin-top-20" label="教师团队:">
                            <CheckboxGroup v-model="teacherIds" @on-change="handleTeacherChange">
                                <Checkbox disabled v-for="(name, id) in teacher" :key='id' :label="id">{{ name }}</Checkbox>
                            </CheckboxGroup>
                        </FormItem>
                        <div class="margin-top-20" style="text-align: center">
                            <Button type="text" size="large" @click="handleBack" style="margin-right: 20px">返回</Button>
                        </div>
                    </Form>
                </Card>
            </Col>
        </Row>
    </div>
</template>

<script>
    import tinymce from 'tinymce';
    import jq from 'jquery';

    export default {
        name: 'course-edit',
        data () {
            return {
                upload_data: {},
                id: 0,
                center_id: '0',
                center: {},
                type_id: [],
                type: [],
                course_title: '',
                status: '1',
                teacher: {},
                teacherIds: [],
                content: '',
                cover_img: ''
            };
        },
        methods: {
            handleFormatError: function () {
                this.$Message.error('文件格式错误');
            },
            handleSizeError: function () {
                this.$Message.error('文件超出大小限制');
            },
            handleSuccess: function (response) {
                // 上传监听
                if (response.status === 1) {
                    this.cover_img = response.data.file_url;
                } else {
                    this.$Message.error(response.msg);
                }
            },
            handleBeforeUpload: function () {
                let that = this;
                this.request({
                    api: '/v1/upload/upload',
                    user_type: 3
                }, function (response) {
                    if (response.status === 1) {
                        that.upload_data = response.data;
                    }
                }, false);
                return new Promise((resolve) => {
                    this.$nextTick(function () {
                        resolve(true);
                    });
                });
            },
            handleTeacherChange: function () {
                console.log(arguments);
            },
            handleUploadVideo: function () {
                this.$router.push({name: 'chapter_edit'});
            },
            handleSubmit: function () {
                let that = this;
                if (this.center_id === '0') {
                    this.$Message.error('请选择来源');
                    return false;
                }
                if (this.type_id.length === 0) {
                    this.$Message.error('请选择分类');
                    return false;
                }
                if (this.course_title === '') {
                    this.$Message.error('标题不能为空');
                    return false;
                }
                if (this.cover_img === '') {
                    this.$Message.error('封面图不能为空');
                    return false;
                }
                if (this.status !== '0' && this.status !== '1') {
                    this.$Message.error('请选择状态');
                    return false;
                }
                if (this.teacherIds.length === 0) {
                    this.$Message.error('请选择老师团队');
                    return false;
                }
                let content = tinymce.get('content').getContent();
                this.request({
                    api: '/v1/course/edit',
                    id: this.id,
                    user_type: 3,
                    course_title: this.course_title,
                    center_id: this.center_id,
                    course_type_id: this.type_id[this.type_id.length - 1],
                    status: this.status,
                    cover_img: this.cover_img,
                    content: content,
                    teacher_ids: this.teacherIds
                }, function (response) {
                    if (response.status === 1) {
                        that.$Modal.info({
                            title: '提示',
                            content: '成功',
                            onOk: function () {
                                that.$router.push({name: 'course_index'});
                            }
                        });
                    } else {
                        that.$Modal.error({title: '提示', content: response.msg});
                    }
                });
            },
            uploadSuccess: function () {
                this.cover_img = '123232';
            },
            handleBack: function () {
                this.$router.go(-1);
            },
            selectData: function (api, selectKey, dataKey, dataVal, param) {
                // 获取并绑定下拉菜单数据
                let that = this;
                param = param || {};
                param['api'] = api;
                param['user_type'] = 3;
                this.request(param, function (response) {
                    if (response.status === 1) {
                        that[selectKey] = that.array_map(response.data, dataKey, dataVal);
                    } else {
                        that[selectKey] = {};
                    }
                });
            },
            getData: function () {
                let that = this;
                this.request({
                    api: '/v1/course/read',
                    id: this.id,
                    user_type: 3
                }, function (response) {
                    if (response.status === 1) {
                        // 数据初始化
                        let data = response.data;
                        that.center_id = data.center_id.toString();
                        that.course_title = data.course_title;
                        that.status = data.status.toString();
                        that.cover_img = data.cover_img;
                        tinymce.get('content').setContent(data.content);
                        that.getMyType(data.other_id);
                        that.getType();
                    } else {
                        that.$Modal.error({content: '课程不存在',
                            onOk: function () {
                                that.$router.push({name: 'course_index'});
                            }
                        });
                    }
                });
            },
            getMyTeacher: function () {
                let that = this;
                this.request({
                    api: '/v1/course/teacher_index',
                    id: this.id,
                    user_type: 3
                },
                function (response) {
                    if (response.status === 1) {
                        that.teacherIds = response.data.map(function (teacher) {
                            return teacher.id.toString();
                        });
                    } else {
                        that.teacherIds = [];
                    }
                });
            },
            getCenter: function () {
                // 获取来源
                this.selectData('/v1/mooc_center/index', 'center', 'id', 'center_name');
            },
            getMyType: function (leafId) {
                // 获取类型树
                let that = this;
                this.request({
                    api: '/v1/course_type/tree_from_leaf',
                    leaf_id: leafId,
                    user_type: 3
                }, function (response) {
                    that.type_id = [];
                    if (response.status === 1) {
                        that.type_id = response.data.map(function (v) {
                            return v.id;
                        });
                    } else {
                        that.type_id = [];
                    }
                });
            },
            getType: function () {
                // 获取类型
                let that = this;
                this.request({
                    api: '/v1/course_type/index',
                    center_id: this.center_id,
                    user_type: 3
                }, function (response) {
                    if (response.status === 1) {
                        that.type = response.data;
                    } else {
                        that.type = [];
                    }
                });
            },
            getTeacher: function () {
                // 获取老师
                this.selectData('/v1/user/index', 'teacher', 'id', 'nick_name', {type: [2], all: 1});
            },
            handleChange: function () {
                this.getType();
            }
        },
        computed: {
        },
        mounted () {
            this.id = this.$route.params.course_id;
            this.getData();
            this.getMyTeacher();
            this.getTeacher();
            this.getCenter();
            let that = this;
            this.request({
                api: '/v1/upload/upload',
                user_type: 3
            }, function (response) {
                if (response.status === 1) {
                    that.upload_data = response.data;
                }
            }, false);
            tinymce.init({
                selector: '#content',
                branding: false,
                elementpath: false,
                height: 300,
                readonly: 1,
                language: 'zh_CN.GB2312',
                menubar: 'edit insert view format table tools',
                theme: 'modern',
                plugins: [
                    'advlist autolink lists link image charmap print preview hr anchor pagebreak imagetools',
                    'searchreplace visualblocks visualchars code fullscreen fullpage',
                    'insertdatetime media nonbreaking save table contextmenu directionality',
                    'emoticons paste textcolor colorpicker textpattern imagetools codesample'
                ],
                toolbar1: ' newnote print fullscreen preview | undo redo | insert | styleselect | forecolor backcolor bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image emoticons media codesample',
                autosave_interval: '20s',
                image_advtab: true,
                table_default_styles: {
                    width: '100%',
                    borderCollapse: 'collapse'
                }
            });
        },
        destroyed () {
            tinymce.get('content').destroy();
        }
    };
</script>
