<style lang="less">
    @import './login.less';
</style>

<template>
    <div class="login" @keydown.enter="handleSubmit">
        <div class="login-con">
            <Card :bordered="false">
                <p slot="title">
                    <Icon type="log-in"></Icon>
                    欢迎登录
                </p>
                <div class="form-con">
                    <Form ref="loginForm" :model="form" :rules="rules">
                        <FormItem prop="userName">
                            <Input v-model="form.userName" placeholder="请输入用户名">
                            <span slot="prepend">
                                    <Icon :size="16" type="person"></Icon>
                                </span>
                            </Input>
                        </FormItem>
                        <FormItem prop="password">
                            <Input type="password" v-model="form.password" placeholder="请输入密码">
                            <span slot="prepend">
                                    <Icon :size="14" type="locked"></Icon>
                                </span>
                            </Input>
                        </FormItem>
                        <FormItem>
                            <Button @click="handleSubmit" type="primary" long>登录</Button>
                        </FormItem>
                    </Form>
                    <!--<p class="login-tip">输入任意用户名和密码即可</p>-->
                </div>
            </Card>
        </div>
    </div>
</template>

<script>
    import Cookies from 'js-cookie';
    import jq from 'jquery';

    export default {
        data () {
            return {
                form: {
                    userName: 'admin',
                    password: ''
                },
                rules: {
                    userName: [
                        {required: true, message: '账号不能为空', trigger: 'blur'}
                    ],
                    password: [
                        {required: true, message: '密码不能为空', trigger: 'blur'}
                    ]
                }
            };
        },
        methods: {
            handleSubmit () {
                this.$refs.loginForm.validate((valid) => {
                    if (valid) {
                        let that = this;
                        jq.ajax({
                            url: 'http://mooc.com/v1/proxy/pass_login',
                            data: {
                                user_login: this.form.userName,
                                user_pass: this.form.password,
                                mode: 3
                            },
                            type: 'post',
                            dataType: 'json',
                            success: function (res) {
                                if (res.status === 1) {
                                    Cookies.set('user', that.form.userName);
                                    Cookies.set('password', that.form.password);
                                    Cookies.set('center_id', res.data.center_id);
                                    // Cookies.set('mooc_center_token', res.data.token);
                                    // Cookies.set('mooc_center_salt', res.data.salt);
                                    if (res.data.center_id === 1) {
                                        Cookies.set('admin_token', res.data.token);
                                        Cookies.set('admin_salt', res.data.salt);
                                    } else {
                                        Cookies.set('center_token', res.data.center_token);
                                        Cookies.set('center_salt', res.data.salt);
                                    }
                                    that.$store.commit('setAvator', 'https://ss1.bdstatic.com/70cFvXSh_Q1YnxGkpoWK1HF6hhy/it/u=3448484253,3685836170&fm=27&gp=0.jpg');
                                    that.$router.push({
                                        name: 'home_index'
                                    });
                                } else {
                                    that.$Modal.info({
                                        title: '提示',
                                        content: res.msg
                                    });
                                }
                            }
                        });
                    }
                });
            }
        }
    };
</script>

<style>

</style>
