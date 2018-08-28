<style lang="less">
    @import './course.less';
</style>

<template>
    <div>
        <Row :gutter="10">
            <Col span="24">
                <Card>
                    <p slot="title">
                        <Icon type="ios-book-outline"></Icon>
                        慕课库管理
                    </p>
                    <Row>
                        <label for="center_id">来源:</label>
                        <Select v-model="center_id" id="center_id" style="width: 100px">
                            <Option v-for="(name, id) in center" :value="id" :key="id">{{ name }}</Option>
                        </Select>
                        <label for="type_id">分类:</label>
                        <Cascader :data="type" v-model="type_id" style="width: 150px;display: inline-block"
                                  id="type_id"></Cascader>
                        <label for="creator_type">创建者类型:</label>
                        <Select v-model="creator_type" @on-change="getCreator" id="creator_type"
                                style="width: 100px">
                            <Option value="1">后台</Option>
                            <Option value="2">老师</Option>
                        </Select>
                        <label for="creator_id">创建者:</label>
                        <Select v-model="creator_id" id="creator_id" style="width: 100px">
                            <Option v-for="(name, id) in creator" :value="id" :key="id">{{ name }}</Option>
                        </Select>
                        <label for="title">标题:</label><Input v-model="title" id="title" placeholder="请输入标题搜索..."
                                                             style="width: 200px"/>
                        <span @click="handleSearch" style="margin: 0 10px;"><Button type="primary"
                                                                                    icon="search">搜索</Button></span>
                        <Button @click="handleCancel" type="ghost">取消</Button>
                    </Row>
                    <!--<Row style="margin-top: 10px">-->
                    <!--<Cascader :data="type" v-model="change_type_id" style="width: 150px;display: inline-block"-->
                    <!--id="change_type_id">-->
                    <!--</Cascader>-->
                    <!--<Button type="error" @click="batchType">批量归类</Button>-->
                    <!--</Row>-->
                    <!--<Row style="margin: 10px 0;">-->
                    <!--<Button type="error" @click="batchDel">批量删除</Button>-->
                    <!--<Button type="error" @click="batchRecommend">批量设为推荐</Button>-->
                    <!--<Button type="primary" @click="locationToAdd">创建课程</Button>-->
                    <!--</Row>-->
                    <Row class="searchable-table-con1" style="margin:10px 0;">
                        <Table :columns="columns" :data="data" ></Table>
                    </Row>
                    <Row>
                        <Page :total="total" :current="current" :page-size="pageSize" @on-change="handlePageChange"
                              show-total show-elevator></Page>
                    </Row>
                </Card>
            </Col>
        </Row>
    </div>
</template>

<script>
    import jq from 'jquery';
    import Cookies from 'js-cookie';

    export default {
        name: 'course_index',
        data: function () {
            let that = this;
            return {
                columns: [
                    // {
                    //     type: 'selection',
                    //     align: 'center',
                    //     width: 60
                    // },
                    {
                        key: 'id',
                        title: 'ID',
                        width: 60
                    },
                    {
                        key: 'course_title',
                        title: '标题'
                    },
                    {
                        key: 'course_type',
                        title: '类型'
                    },
                    {
                        key: 'center_name',
                        title: '来源'
                    },
                    {
                        key: 'type',
                        title: '创建者类型',
                        render: function (h, params) {
                            return h('span', {}, params.row.type === 1 ? '后台' : '老师');
                        }
                    },
                    {
                        key: 'creator',
                        title: '创建者'
                    },
                    {
                        key: 'teachers',
                        title: '老师'
                    },
                    {
                        key: 'chapter_num',
                        title: '章节数量'
                    },
                    {
                        key: 'play_num',
                        title: '访问量'
                    },
                    {
                        key: 'baoming_num',
                        title: '学生数'
                    },
                    {
                        key: 'qa_num',
                        title: '问答数'
                    },
                    {
                        key: 'note_num',
                        title: '笔记数'
                    },
                    {
                        key: 'comment_num',
                        title: '评论数量'
                    },
                    {
                        key: 'action',
                        title: '操作',
                        width: 100,
                        render: (h, params) => {
                            return h('div', [
                                h('Button', {
                                    props: {
                                        type: 'primary',
                                        size: 'small'
                                    },
                                    style: {
                                        marginRight: '5px'
                                    },
                                    on: {
                                        click: () => {
                                            that.$router.push({name: 'course_detail', params: {id: params.row.id}});
                                            // this.show(params.index)
                                        }
                                    }
                                }, '查看详情')]);
                        }
                    }
                ],
                data: [],
                title: '',
                center_id: 0,
                type_id: [],
                creator_id: 0,
                creator_type: 1,
                center: {},
                type: [],
                creator: {},
                total: 0,
                current: 1,
                pageSize: 10,
                selectIds: [],
                // change_type_id: [],
                cid: 0
            };
        },
        created: function () {
            this.cid = Cookies.get('center_id');
            // this.cid = '14';
            this.getData();
            this.getCenter();
            this.getType();
            this.getCreator();
        },
        methods: {
            init: function () {
                // 初始化检索条件
                this.title = '';
                this.center_id = 0;
                this.type_id = [];
                this.type = [];
                this.creator_id = 0;
                this.current = 1;
                this.total = 0;
            },
            selectData: function (api, selectKey, dataKey, dataVal, type, param) {
                // 获取并绑定下拉菜单数据
                let that = this;
                param = param || {};
                param['api'] = api;
                param['user_type'] = 3;
                this.request(param, function (response) {
                    console.log(response);
                    if (response.status === 1) {
                        if (type === 1) {
                            // 无list
                            that.center = that.array_map(response.data, dataKey, dataVal);
                        } else {
                            that[selectKey] = that.array_map(response.data.list, dataKey, dataVal);
                        }
                    } else {
                        that[selectKey] = {};
                    }
                });
            },
            getData: function () {
                // 获取列表数据
                let that = this;
                let type = this.type_id.length === 0 ? 0 : this.type_id[this.type_id.length - 1];
                this.request({
                    api: '/v1/statistics/getCourses',
                    user_type: 3,
                    page: this.current,
                    len: this.pageSize,
                    center_id: this.center_id,
                    creator_type: this.creator_type,
                    creator_id: this.creator_id,
                    title: this.title,
                    cid: this.cid,
                    type: type
                }, function (response) {
                    if (response.status === 1) {
                        that.data = response.data.list;
                        that.total = response.data.num;
                    } else {
                        that.init();
                    }
                });
            },
            getCenter: function () {
                // 获取来源
                this.selectData('/v1/mooc_center/index', 'center', 'id', 'center_name', 1);
            },
            getType: function () {
                // 获取类型
                let that = this;
                this.request({
                    api: '/v1/course_type/index',
                    user_type: 3,
                    all: 1
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
                this.selectData('/v1/user/index', 'creator', 'id', 'nick_name', 2, {type: [2], all: 1});
            },
            getCreator: function () {
                // 获取创建者
                if (this.creator_type === '2') {
                    this.getTeacher();
                } else {
                    this.selectData('/v1/mooc_center/index', 'creator', 'id', 'center_name');
                }
            },
            handleSearch: function () {
                // 搜索
                this.current = 1;
                this.getData();
            },
            handleCancel: function () {
                // 清空搜索
                this.init();
                this.getData();
            },
            handlePageChange: function (cur) {
                // 分页
                this.current = cur;
                this.getData();
            },
            recommend: function (id) {
                // 推荐
                let recommendBtn = jq('#recommend-' + id);
                let recommendText = recommendBtn.text().trim();
                let recommend = recommendText === '推荐' ? 1 : 0;
                let that = this;
                this.request({
                    api: '/v1/course/recommend',
                    user_type: 3,
                    recommend: recommend,
                    id: id
                }, function (response) {
                    if (response.status === 1) {
                        if (recommendText === '推荐') {
                            that.$Message.success('推荐成功');
                            recommendBtn.html('<i class="ivu-icon ivu-icon-flag"></i><span>取消推荐</span>').removeClass('ivu-btn-primary').addClass('ivu-btn-info');
                        } else {
                            that.$Message.success('取消推荐成功');
                            recommendBtn.html('<i class="ivu-icon ivu-icon-flag"></i><span>推荐</span>').removeClass('ivu-btn-info').addClass('ivu-btn-primary');
                        }
                    } else {
                        that.$Message.error(response.msg);
                    }
                });
            },
            delCourse: function (id) {
                // 删除课程
                let that = this;
                this.request({
                    api: '/v1/course/delete',
                    user_type: 3,
                    id: id
                }, function (response) {
                    if (response.status === 1) {
                        that.$Message.success('删除成功');
                        that.getData();
                    } else {
                        that.$Message.error(response.msg);
                    }
                });
            }
        }
    };
</script>
