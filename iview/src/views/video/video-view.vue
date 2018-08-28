<style lang="less">
    @import '../../styles/common.less';
    @import './video-edit.less';
</style>

<template>
    <div>
        <Row>
            <Col span="18">
                <Card>
                    <Form :label-width="80">
                        <FormItem label="标题" :error="titleError">
                            <Input v-model="title"  disabled icon="android-list"/>
                        </FormItem>
                        <FormItem label="来源" :error="titleError" disabled>
                            <Select  v-model='center_id' disabled>
                                <Option v-for="(name, id) in centerList" :value="id" :key="id" >{{ name }}</Option>
                            </Select>
                        </FormItem>
                        <FormItem v-model="status" class="margin-top-20" label="状态:">
                            <RadioGroup v-model="status" disabled>
                                <Radio label="1">显示</Radio>
                                <Radio label="0">隐藏</Radio>
                            </RadioGroup>
                        </FormItem>
                        <FormItem label="缩略图" disabled>
                            <!--<Upload action="http://mooc.com/v1/upload/upload"-->
                                    <!--:accept="'image/*'"-->
                                    <!--:format="['jpg','png','gif']"-->
                                    <!--:show-upload-list="false"-->
                                    <!--:max-size="1024"-->
                                    <!--:data='upload_data'-->
                                    <!--:on-success="handleSuccess"-->
                                    <!--:before-upload="handleBeforeUpload"-->
                                    <!--:on-format-error="handleFormatError"-->
                                    <!--:on-exceeded-size="handleSizeError">-->
                                <img v-if="cover_img" :src="cover_img" disabled style="width: 120px;cursor: pointer" alt="">
                                <img v-else src="../../images/image.png" disabled style="width: 120px;cursor: pointer" alt="">
                            <!--</Upload>-->
                        </FormItem>
                        <FormItem label="视频" disabled>
                            <Col span="2" disabled>
                                <!--<Upload action="http://mooc.com/v1/upload/upload"-->
                                        <!--:accept="'video/*'"-->
                                        <!--:format="['mp4']"-->
                                        <!--:show-upload-list="false"-->
                                        <!--:max-size="204800"-->
                                        <!--:data='upload_data'-->
                                        <!--:on-success="handleVideoSuccess"-->
                                        <!--:before-upload="handleBeforeUpload"-->
                                        <!--:on-format-error="handleFormatError"-->
                                        <!--:on-exceeded-size="handleSizeError">-->
                                    <Button type="primary" disabled><Icon type="ios-videocam"></Icon> 视频上传</Button>
                                <!--</Upload>-->
                            </Col>
                            <Col span="18">
                                <Input v-model="video_url" disabled/>
                            </Col>
                        </FormItem>
                        <FormItem>
                            <Button type="primary" @click="SubmitEdit">Submit</Button>
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
                title: '',
                titleError: '',
                source: 0,
                center_id: 0,
                status: '0',
                centerList: {},
                upload_data: {},
                thumb: '',
                cover_img: '',
                video_url: ''
            };
        },
        created: function () {
            this.getDetail();
            this.getCenterList();
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
                    this.cover_img = response.data.file_url;
                    this.thumb = response.data.img_url;
                } else {
                    this.$Message.error(response.msg);
                }
            },
            handleVideoSuccess: function (response) {
                if (response.status === 1) {
                    this.video_url = response.data.file_url;
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
            getDetail: function () {
                var that = this;
                this.id = this.$route.params.video_id;
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {
                        params:
                            {
                                user_type: 3,
                                api: '/v1/video/read',
                                id: this.id
                            }
                    }
                ).then(function (response) {
                    response = response.data;
                    console.log(response);
                    if (response.status === 1) {
                        that.title = response.data.title;
                        that.center_id = response.data.center_id.toString();
                        that.status = response.data.status.toString();
                        that.cover_img = response.data.thumb;
                        console.log(response.data.thumb);
                        console.log(that.cover_img);
                        that.video_url = response.data.url;
                        if (response.data.type === 1) {
                            that.getCenterList(1);
                        } else {
                            that.getTeacherList();
                        }
                    }
                }).catch(function (err) {
                    console.log(err.stack);
                });
            },
            getCenterList: function () {
                let that = this;
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {
                        params:
                            {
                                api: '/v1/mooc_center/index',
                                user_type: 3,
                                all: 1
                            }
                    }
                ).then(function (response) {
                    response = response.data;
                    if (response.status === 1) {
                        that.centerList = that.array_map(response.data, 'id', 'center_name');
                    } else {
                        that.centerList = {};
                    }
                }).catch(function (err) {
                    console.log(err.stack);
                });
            },
            getTeacherList: function () {
                var that = this;
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {
                        params:
                            {
                                api: '/v1/user/index',
                                user_type: 3,
                                type: [2],
                                all: 1
                            }
                    }
                ).then(function (response) {
                    response = response.data;
                    if (response.status === 1) {
                        that.uploderList = that.array_map(response.data, 'id', 'nick_name');
                    } else {
                        that.uploderList = {};
                    }
                }).catch(function (err) {
                    console.log(err.stack);
                });
            },
            change_type: function () {
                if (this.type === '1') {
                    // 后台
                    this.getCenterList(1);
                } else {
                    this.getTeacherList();
                }
            },
            SubmitEdit: function () {
                let that = this;
                this.id = this.$route.params.video_id;
                if (this.title.length === 0) {
                    this.$Message.error('标题不能为空');
                    return false;
                }
                if (this.center_id === 0) {
                    this.$Message.error('请选择来源');
                    return false;
                }
                if (this.thumb === '') {
                    this.$Message.error('请上传缩略图');
                    return false;
                }
                if (this.url === '') {
                    this.$Message.error('请上传视频');
                    return false;
                }
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {
                        params:
                            {
                                user_type: 3,
                                api: '/v1/video/update',
                                id: this.id,
                                title: this.title,
                                center_id: this.center_id,
                                status: this.status,
                                thumb: this.thumb,
                                url: this.video_url
                            }
                    }
                ).then(function (response) {
                    response = response.data;
                    if (response.status === 1) {
                        that.$Modal.info({
                            title: '修改',
                            content: '修改视频成功',
                            onOk: function () {
                                that.$router.push({name: 'video_index'});
                            }
                        });
                    } else {
                        that.$Message.error('修改视频失败');
                    }
                }).catch(function (err) {
                    console.log(err.stack);
                });
            },
            handleBack: function () {
                this.$router.go(-1);
            }
        }
    };
</script>
