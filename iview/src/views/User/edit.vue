<style lang="less">
    @import './edit.less';
</style>

<template>
    <div>
        <Row>
            <Col span="18">
                <Card>
                    <Form :label-width="80">
                        <FormItem label="用户名">
                            <Input v-model="user_login" icon="android-list"/>
                        </FormItem>
                        <FormItem label="密码">
                            <Input v-model="user_pass" icon="android-list"/>
                        </FormItem>
                        <FormItem label="确认密码">
                            <Input v-model="confirm_pass" icon="android-list"/>
                        </FormItem>
                        <FormItem label="职称">
                            <Input v-model="teacher_title" icon="android-list"/>
                        </FormItem>
                        <FormItem label="昵称">
                            <Input v-model="nick_name" icon="android-list"/>
                        </FormItem>
                        <FormItem label="单位">
                            <Input v-model="department" icon="android-list"/>
                        </FormItem>
                        <FormItem class="margin-top-20" label="简介:">
                            <textarea name="profile" v-model="profile" id="profile" cols="180" rows="5">
                            </textarea>
                        </FormItem>
                        <FormItem label="头像">
                            <Upload action="http://mooc.com/v1/upload/upload"
                                    :accept="'image/*'"
                                    :format="['jpg','png','gif']"
                                    :show-upload-list="false"
                                    :max-size="1024"
                                    :data='upload_data'
                                    :on-success="handleSuccess"
                                    :before-upload="handleBeforeUpload"
                                    :on-format-error="handleFormatError"
                                    :on-exceeded-size="handleSizeError">
                                <img v-if="head_portrait" :src="head_portrait" style="width: 120px;cursor: pointer" alt="">
                                <img v-else src="../../images/image.png" style="width: 120px;cursor: pointer" alt="">
                            </Upload>
                        </FormItem>
                        <FormItem v-model='status' class="margin-top-20" label="状态:">
                            <RadioGroup v-model="status">
                                <Radio label="1">启用</Radio>
                                <Radio label="0">禁用</Radio>
                            </RadioGroup>
                        </FormItem>
                        <FormItem>
                            <Button type="primary" @click="SubmitUser">Submit</Button>
                            <Button type="ghost" style="margin-left: 8px" @click="handleBack">Cancel</Button>
                        </FormItem>
                    </Form>
                </Card>
            </Col>
        </Row>
    </div>
</template>

<script>
    import axios from 'axios';
    import tinymce from 'tinymce';


    export default {
        name: 'video-edit',
        data: function () {
            return {
                user_login: '',
                user_pass: '',
                confirm_pass: '',
                teacher_title: '',
                nick_name: '',
                avatar: '',
                head_portrait: '',
                department: '',
                status: '1',
                upload_data: {},
                profile: ''
            };
        },
        created: function () {
            this.getUserInfo();
        },
        methods: {
            handleFormatError: function () {
                this.$Message.error('文件格式错误');
            },
            handleSizeError: function () {
                this.$Message.error('文件超出大小限制');
            },
            handleSuccess: function (response) {
                if (response.status === 1) {
                    this.head_portrait = response.data.file_url;
                    this.avatar = response.data.img_url;
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
            request1: function (params, callback1) {
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {params: params}
                ).then(callback1).catch();
            },
            getUserInfo: function () {
                let that = this;
                let id = this.$route.params.id;
                this.request1({
                    api: '/v1/user/read',
                    user_type: 3,
                    id: id,
                    type: 1
                }, function (response) {
                    let res = response.data;
                    console.log(res);
                    if (res.status === 1) {
                        that.user_login = res.data.user_login;
                        that.teacher_title = res.data.teacher_title;
                        that.nick_name = res.data.nick_name;
                        that.department = res.data.department;
                        that.avatar = res.data.avatar;
                        that.status = res.data.status.toString();
                        that.profile = res.data.profile;
                    } else {
                        that.$Message.error(res.msg);
                    }
                }

                );
            },
            SubmitUser: function () {
                let that = this;
                let id = this.$route.params.id;
                if (this.user_login.length === 0) {
                    this.$Message.error('用户名不能为空');
                    return false;
                }
                if (this.user_pass.length !== 0) {
                    if (this.confirm_pass.length === 0) {
                        this.$Message.error('确认密码不能为空');
                        return false;
                    }

                    if (this.confirm_pass !== this.user_pass) {
                        this.$Message.error('密码与确认密码不一致');
                        return false;
                    }
                } else {
                    this.confirm_pass = '';
                }
                let profile = tinymce.get('profile').getContent();
                that.request1(
                    {
                        user_type: 3,
                        api: '/v1/user/edit',
                        user_login: this.user_login,
                        user_pass: this.user_pass,
                        confirm_pass: this.confirm_pass,
                        nick_name: this.nick_name,
                        teacher_title: this.teacher_title,
                        department: this.department,
                        profile: profile,
                        avatar: this.avatar,
                        status: this.status,
                        type: 2,
                        id: id
                    },
                    function (response) {
                        response = response.data;
                        if (response.status === 1) {
                            that.$Modal.info({
                                title: '修改',
                                content: '修改用户信息成功',
                                onOk: function () {
                                    that.$router.push({name: 'user_index'});
                                }
                            });
                        } else {
                            that.$Message.error(response.msg);
                        }
                    }
                );
            },
            handleBack: function () {
                this.$router.go(-1);
            }
        },
        mounted () {
            tinymce.init({
                selector: '#profile',
                branding: false,
                elementpath: false,
                height: 300,
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
            tinymce.get('profile').destroy();
        }
    };
</script>
