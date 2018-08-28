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
                        教学统计详情
                    </p>
                    <Form ref="courseForm" class="margin-top-20" :label-width="80">
                        <FormItem class="margin-top-20" label="教师:">
                            <Input v-model="nick_name" disabled style="width: auto"/>
                        </FormItem>
                        <FormItem class="margin-top-20" label="职称:">
                            <Input v-model="teacher_title" disabled style="width: auto"/>
                        </FormItem>
                        <FormItem class="margin-top-20" label="单位:">
                            <Input v-model="department" disabled style="width: auto"/>
                        </FormItem>
                        <FormItem class="margin-top-20" label="文化馆:">
                            <Input v-model="venue" disabled style="width: auto"/>
                        </FormItem>
                        <Row>
                            <Col span="24">
                                <Card>
                                    <p slot="title">
                                        <Icon type="md-checkmark-circle"/>
                                        关注人
                                    </p>
                                    <p v-if="follow">
                                        <span style="display: block;magin-top:8px;margin-bottom: 5px;" v-for="(val) in follow">
                                            <span style="display: inline-block;margin-left: 10px" v-for="(value) in val">{{value.nick_name}}</span>
                                        </span>
                                    </p>
                                    <p v-else>
                                        <span>暂无数据</span>
                                    </p>
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
                    </Form>
                </Card>
            </Col>
        </Row>

        <Row>
            <Col span="24">
                <Card>
                    <p slot="title">
                        <Icon type="md-checkmark-circle"/>
                        慕课评论
                    </p>
                    <p v-if="comments">
                        <span style="display: block;magin-top:8px;margin-bottom: 5px;" v-for="(val) in comments"
                              :key="id" :value="id">&nbsp;&nbsp;&nbsp;{{val}}
                            <span style="display: inline-block;float: right;">--{{val}}</span>
                            <hr/>
                        </span>
                    </p>
                    <p v-else>
                        <span>暂无数据</span>
                    </p>
                </Card>
            </Col>
        </Row>

        <Row>
            <Col span="24">
                <Card>
                    <p slot="title">
                        <Icon type="md-checkmark-circle"/>
                        问题
                    </p>
                    <p v-if="questions">
                        <span style="display: block;magin-top:8px;margin-bottom: 5px;" v-for="(val) in questions"
                              :key="id" :value="id">&nbsp;&nbsp;&nbsp;{{val}}
                            <span style="display: inline-block;float: right;">--{{val}}</span>
                            <hr/>
                        </span>
                    </p>
                    <p v-else>
                        <span>暂无数据</span>
                    </p>
                </Card>
            </Col>
        </Row>

        <Row>
            <Col span="24">
                <Card>
                    <p slot="title">
                        <Icon type="md-checkmark-circle"/>
                        笔记
                    </p>
                    <p v-if="section_notes">
                        <span style="display: block;magin-top:8px;margin-bottom: 5px;" v-for="(val) in section_notes"
                              :key="id" :value="id">&nbsp;&nbsp;&nbsp;{{val}}
                            <span style="display: inline-block;float: right;">--{{val}}</span>
                            <hr/>
                        </span>
                    </p>
                    <p v-else>
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
        name: "teacher-detail",


        data() {
            return {
                nick_name: '',
                teacher_title: '',
                department: '',
                venue: '',
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
                comments: {},
                notes: {},
                questions: {}
            };
        },

        methods: {
            getData: function () {
                let that = this;
                this.id = this.$route.params.id;
                this.request({
                    api: '/v1/chen/getTeacherByID',
                    id: this.id,
                }, function (response) {
                    if (response.status === 1) {
                        // 数据初始化
                        let data = response.data;
                        that.nick_name = data.info.nick_name;
                        that.teacher_title = data.info.teacher_title;
                        that.department = data.info.department
                        that.venue = data.center_name;
                        that.follow = response.data.follow;
                        that.comments = response.data.comments;
                        that.questions = response.data.questions;
                        that.section_notes = response.data.section_notes;
                        if (response.data.chapter.length !== 0) {
                            that.data1 = response.data.chapter;
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
                        that.$Modal.error({
                            content: '课程不存在',
                            onOk: function () {
                                that.$router.push({name: 'statistics_course'});
                            }
                        });
                    }
                });
            },
        },
        mounted() {
            this.getData();
        }
    }
</script>

<style scoped>

</style>