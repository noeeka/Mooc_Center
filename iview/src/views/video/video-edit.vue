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
                            <Input v-model="title"  icon="android-list"/>
                        </FormItem>
                        <FormItem label="来源" :error="titleError">
                            <Select  v-model='center_id' disabled>
                                <Option v-for="(name, id) in centerList" :value="id" :key="id" >{{ name }}</Option>
                            </Select>
                        </FormItem>
                        <FormItem v-model="status" class="margin-top-20" label="状态:">
                            <RadioGroup v-model="status">
                                <Radio label="1">显示</Radio>
                                <Radio label="0">隐藏</Radio>
                            </RadioGroup>
                        </FormItem>
                        <FormItem label="缩略图">
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
                                <img v-if="cover_img" :src="cover_img" style="width: 120px; cursor: pointer; height: 120px; display: inline-block; background-color: rgb(226, 226,226); alt="">
                                <img v-else src="../../images/image.png" style="width: 120px;cursor: pointer" alt="">
                            </Upload>
                        </FormItem>
                        <FormItem label="视频">
                            <Col span="2">
                                <Upload action="http://mooc.com/v1/upload/upload"
                                        :accept="'video/*'"
                                        :format="['mp4']"
                                        :show-upload-list="false"
                                        :max-size="204800"
                                        :data='upload_data'
                                        :on-success="handleVideoSuccess"
                                        :before-upload="handleBeforeUpload"
                                        :on-format-error="handleFormatError"
                                        :on-exceeded-size="handleSizeError">
                                    <Button type="primary"><Icon type="ios-videocam"></Icon> 视频上传</Button>
                                </Upload>
                            </Col>
                            <Col span="18">
                                <Input v-model="video_url"/>
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
                video_url: '',
                video_time: 0
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
                    this.video_time = response.data.total_time;
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
                this.request(
                    {
                        user_type: 3,
                        api: '/v1/video/read',
                        id: this.id
                    },
                    function (response) {
                        if (response.status === 1) {
                            that.title = response.data.title;
                            that.center_id = response.data.center_id.toString();
                            that.status = response.data.status.toString();
                            that.cover_img = response.data.thumb;
                            that.thumb = response.data.thumb;
                            that.video_url = response.data.url;
                        }
                    }
                );
            },
            getCenterList: function () {
                let that = this;
                this.request(
                    {
                        api: '/v1/mooc_center/index',
                        user_type: 3,
                        all: 1
                    },
                    function (response) {
                        if (response.status === 1) {
                            that.centerList = that.array_map(response.data, 'id', 'center_name');
                        } else {
                            that.centerList = {};
                        }
                    }
                );
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

                this.request(
                    {
                        user_type: 3,
                        api: '/v1/video/update',
                        id: this.id,
                        title: this.title,
                        center_id: this.center_id,
                        status: this.status,
                        thumb: this.thumb,
                        url: this.video_url,
                        video_time: this.video_time
                    },
                    function (response) {
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
                    }
                );
            },
            handleBack: function () {
                this.$router.go(-1);
            }
        }
    };
</script>
