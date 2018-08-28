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
                            <Select v-model="center_id" id="center_id" style="width: 300px" @on-change="handleChange">
                                <Option v-for="(name, id) in center" :value="id" :key="id">{{ name }}</Option>
                            </Select>
                            <label for="type_id">分类:</label>
                            <Cascader :data="type" v-model="type_id" style="width: 150px;display: inline-block" id="type_id"></Cascader>
                        </FormItem>
                        <FormItem class="margin-top-20" label="标题:">
                            <Input v-model="course_title" icon="android-list"/>
                        </FormItem>
                        <FormItem class="margin-top-20" label="封面图:">
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
                                <img v-if="cover_img" :src='cover_img+"?width=120&height=120"' style="width: 120px;cursor: pointer;height: 120px;display: inline-block;background-color: #e2e2e2;" alt="">
                                <img v-else src="../../images/image.png" style="width: 120px;cursor: pointer;height: 120px;display: inline-block;background-color: #e2e2e2;" alt="">
                            </Upload>
                        </FormItem>
                        <FormItem v-model='status' class="margin-top-20" label="状态:">
                            <RadioGroup v-model="status">
                                <Radio label="1">显示</Radio>
                                <Radio label="0">隐藏</Radio>
                            </RadioGroup>
                        </FormItem>
                        <FormItem class="margin-top-20" label="简介:">
                            <textarea name="content" v-model="content" id="content" cols="180" rows="5">
                            </textarea>
                        </FormItem>
                        <FormItem v-model='open_status' class="margin-top-20" label="开放状态:">
                            <RadioGroup v-model="open_status" @on-change="changeStatus" >
                                <Radio label="0">不开放</Radio>
                                <Radio label="1">限时开放</Radio>
                                <Radio label="2">开放</Radio>
                            </RadioGroup>
                        </FormItem>
                        <FormItem class="margin-top-20" label="开放时间:" id="time" style="display: none;">
                            <DatePicker type="date" @on-change="getStartTime" placeholder="选择日期" style="width: 200px"></DatePicker>
                            <span>至</span>
                            <DatePicker type="date" @on-change="getEndTime" placeholder="选择日期" style="width: 200px"></DatePicker>
                        </FormItem>
                        <FormItem class="margin-top-20" label="关键词:">
                            <Input v-model="keyword"  id="keyword" placeholder="" style="width:940px;"/>
                        </FormItem>
                        <FormItem class="margin-top-20" label="教师团队:">
                            <CheckboxGroup v-model="teacherIds">
                                <Checkbox v-for="(name, id) in teacher" :key='id' :label="id">{{ name }}</Checkbox>
                            </CheckboxGroup>
                        </FormItem>
                        <div class="margin-top-20" style="text-align: center">
                            <Button type="text" size="large" @click="handleBack" style="margin-right: 20px">返回</Button>
                            <Button type="primary" @click="handleSubmit" size="large">提交</Button>
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
    name: 'course-add',
    data () {
        return {
            upload_data: {},
            center_id: '0',
            center: {},
            type_id: [],
            type: [],
            course_title: '',
            status: '1',
            open_status: '2',
            teacher: {},
            teacherIds: [],
            content: '',
            keyword: '',
            cover_img: '',
            img_url: '',
            start_time: 0,
            end_time: 0
        };
    },
    methods: {
        handleFormatError: function () {
            this.$Message.error('文件格式错误');
        },
        handleSizeError: function () {
            this.$Message.error('文件超出大小限制');
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
        handleSuccess: function (response) {
            // 上传监听
            if (response.status === 1) {
                this.cover_img = response.data.file_url;
                this.img_url = response.data.img_url;
            } else {
                this.$Message.error(response.msg);
            }
        },
        getStartTime: function (startTime) {
            this.start_time = this.timeToTimestamp(startTime);
        },
        getEndTime: function (endTime) {
            this.end_time = this.timeToTimestamp(endTime);
        },
        timeToTimestamp: function (time) {
            return new Date(time).getTime() / 1000;
        },
        changeStatus: function (open) {
            if (open === '1') {
                jq('#time').show();
            } else {
                jq('#time').hide();
            }
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
            if (this.img_url === '') {
                this.$Message.error('封面图不能为空');
                return false;
            }
            if (this.status !== '0' && this.status !== '1') {
                this.$Message.error('请选择状态');
                return false;
            }
            if (this.open_status === '1') {
                if (this.start_time === 0) {
                    this.$Message.error('请选择开始时间');
                    return false;
                }
                if (this.end_time === 0) {
                    this.$Message.error('请选择结束时间');
                    return false;
                }
            }
            if (this.teacherIds.length === 0) {
                this.$Message.error('请选择老师团队');
                return false;
            }
            let content = tinymce.get('content').getContent();
            this.request({
                api: '/v1/course/add',
                user_type: 3,
                course_title: this.course_title,
                center_id: this.center_id,
                course_type_id: this.type_id[this.type_id.length - 1],
                status: this.status,
                cover_img: this.img_url,
                content: content,
                keyword: this.keyword,
                teacher_ids: this.teacherIds,
                open_status: this.open_status,
                start_time: this.start_time,
                end_time: this.end_time
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
                console.log(response);
                if (response.status === 1) {
                    that[selectKey] = that.array_map(response.data, dataKey, dataVal);
                } else {
                    that[selectKey] = {};
                }
            });
        },
        getCenter: function () {
            // 获取来源
            this.selectData('/v1/mooc_center/index', 'center', 'id', 'center_name');
        },
        getType: function () {
            // 获取类型
            let that = this;
            this.request({
                api: '/v1/course_type/index',
                center_id: this.center_id,
                user_type: 3
            }, function (response) {
                that.type_id = [];
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
