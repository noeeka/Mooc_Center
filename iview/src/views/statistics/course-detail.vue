<style lang="less">
    @import '../../styles/common.less';
    @import './course-detail.less';
</style>

<template>
    <div>
        <Row>
            <Col span="24">
                <Card>
                    <p slot="title">
                        <Icon type="ios-book-outline"></Icon>
                        慕课简介
                    </p>
                    <Form ref="courseForm" class="margin-top-20" :label-width="80">
                        <FormItem class="margin-top-20" label="来源:">
                            <Select v-model="center_id" disabled  id="center_id" style="width: 300px" >
                                <Option v-for="(name, id) in center" :value="id" :key="id">{{ name }}</Option>
                            </Select>
                            <label for="type_id">分类:</label>
                            <Cascader :data="type" disabled v-model="type_id" style="width: 150px;display: inline-block" id="type_id"></Cascader>
                        </FormItem>
                        <FormItem class="margin-top-20" label="标题:">
                            <Input v-model="course_title" disabled icon="android-list"/>
                        </FormItem>
                        <FormItem class="margin-top-20" label="封面图:">
                            <img v-if="cover_img" :src="cover_img" style="width: 120px;height: 120px;" alt="">
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
                        <!--<FormItem class="margin-top-20" label="上传视频:">-->
                            <!--<Button type="primary" disabled><Icon type="ios-film"></Icon>&nbsp;上传视频</Button>-->
                        <!--</FormItem>-->
                        <!--<FormItem class="margin-top-20" label="教师团队:">-->
                            <!--<CheckboxGroup v-model="teacherIds">-->
                                <!--<Checkbox disabled v-for="(name, id) in teacher" :key='id' :label="id">{{ name }}</Checkbox>-->
                            <!--</CheckboxGroup>-->
                        <!--</FormItem>-->
                        <!--<div class="margin-top-20" style="text-align: center">-->
                            <!--<Button type="text" size="large" @click="handleBack" style="margin-right: 20px">返回</Button>-->
                        <!--</div>-->
                    </Form>
                </Card>
            </Col>
        </Row>
        <Row>
            <Col span="24">
                <Card>
                    <p slot="title">
                        <Icon type="md-checkmark-circle" />
                        慕课老师
                    </p>
                    <Collapse v-model="value1">
                        <Panel v-for="(val,id) in teachers" :value="id" :key="id" name="id">
                            {{val.name}}
                            <!--史蒂夫·乔布斯-->
                            <p slot="content">{{val.profile}}</p>
                        </Panel>
                    </Collapse>
                </Card>
            </Col>
        </Row>
        <Row>
            <Col span="24">
                <Card>
                    <p slot="title">
                        <Icon type="android-menu"></Icon>
                        章节目录
                    </p>
                    <Tree :data="data1"></Tree>
                </Card>
            </Col>
        </Row>
        <Row>
            <Col span="24">
                <Card>
                    <p slot="title">
                        <Icon type="md-checkmark-circle" />
                        慕课问答
                    </p>
                    <Collapse v-model="value2" >
                        <Panel v-for="(val,id) in questions" :value="id" :key="id" name="note">
                            {{val.content}} <span style="display: inline-block;float:right;margin-right:20px;">---{{val.nick_name}}</span>
                            <p slot="content" v-for="(msg,Id) in val.children"  :value="Id" :key="Id" >{{msg.content}}
                                <span style="display: inline-block;float:right;margin-right:20px;">---{{msg.nick_name}}</span>
                            </p>
                        </Panel>
                    </Collapse>
                </Card>
            </Col>
        </Row>
        <Row>
            <Col span="24">
                <Card>
                    <p slot="title">
                        <Icon type="md-checkmark-circle" />
                        慕课笔记
                    </p>
                    <Collapse v-model="value1">
                        <Panel v-for="(val,id) in notes" :value="id" :key="id" name="note">
                            {{val.content}} <span style="display: inline-block;float:right;margin-right:20px;">---{{val.nick_name}}</span>
                                <p slot="content" v-for="(msg,Id) in val.children"  :value="Id" :key="Id" >{{msg.content}}
                                    <span style="display: inline-block;float:right;margin-right:20px;">---{{msg.nick_name}}</span>
                                </p>
                        </Panel>
                    </Collapse>
                </Card>
            </Col>
        </Row>
        <Row>
            <Col span="24">
                <Card>
                    <p slot="title">
                        <Icon type="md-checkmark-circle" />
                        慕课评论
                    </p>
                    <p v-if="comments">
                        <span style="display: block;magin-top:8px;margin-bottom: 5px;" v-for="(val,id) in comments" :key="id" :value="id">&nbsp;&nbsp;&nbsp;{{val.content}}
                            <span style="display: inline-block;float: right;">--{{val.nick_name}}</span>
                            <hr/>
                        </span>
                    </p>
                    <p  v-else>
                        <span>暂无数据</span>
                    </p>
                </Card>
            </Col>
        </Row>

    </div>
</template>

<script>
    import tinymce from 'tinymce';
    import jq from 'jquery';
    import Column from "../column/column";

    export default {
        name: 'course-edit',
        components: {Column},
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
                cover_img: '',
                data1: [
                    {
                        title: '课程目录',
                        expand: true,
                        children: [
                            {
                                title: 'parent 1-1',
                                expand: true,
                                children: [
                                    {
                                        title: 'leaf 1-1-1'
                                    },
                                    {
                                        title: 'leaf 1-1-2'
                                    }
                                ]
                            },
                            {
                                title: 'parent 1-2',
                                expand: true,
                                children: [
                                    {
                                        title: 'leaf 1-2-1'
                                    },
                                    {
                                        title: 'leaf 1-2-1'
                                    }
                                ]
                            }
                        ]
                    }
                ],
                teachers: {},
                value1: '1',
                value2: '2',
                comments: {},
                // notes: {1: {content: '123', nick_name: 'ad', children: {2: {content: 'sdjk', nick_name: 'nick_name'}, 3: {content: '123', nick_name: 'dfasj'}}}}
                notes: {},
                questions: {}
            };
        },
        methods: {
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
                this.id = this.$route.params.id;
                this.request({
                    api: '/v1/statistics/readCourse',
                    id: this.id,
                    user_type: 3
                }, function (response) {
                    console.log(response);
                    if (response.status === 1) {
                        // 数据初始化
                        let data = response.data;
                        that.center_id = data.center_id.toString();
                        that.course_title = data.course_title;
                        that.status = data.status.toString();
                        that.cover_img = data.cover_img;
                        that.teachers = response.data.teacher_team;
                        that.notes = response.data.note_detail;
                        if (response.data.chapter_section.length !== 0) {
                            that.data1[0].children = response.data.chapter_section;
                        } else {
                            that.data1 = [];
                        }
                        // if (response.data.qa_detail.length !== 0) {
                        //     that.data2[0].children = response.data.qa_detail;
                        // } else {
                        //     that.data2 = [];
                        // }
                        if (response.data.comment.length !== 0) {
                            that.comments = that.array_map(response.data.comment, 'id', 'content');
                        } else {
                            that.comments = false;
                        }
                        tinymce.get('content').setContent(data.content);
                        that.getMyType(data.other_id);
                        that.getType();
                    } else {
                        that.$Modal.error({content: '课程不存在',
                            onOk: function () {
                                that.$router.push({name: 'statistics_course'});
                            }
                        });
                    }
                });
            },
            getCenter: function () {
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
            handleChange: function () {
                this.getType();
            }
        },
        computed: {
        },
        mounted () {
            this.id = this.$route.params.course_id;
            this.getData();
            // this.getTeacher();
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
