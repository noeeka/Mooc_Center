<style lang="less">
    @import './video.less';
</style>

<template>
    <div>
        <Row :gutter="10">
            <Col>
                <Card>
                    <p slot="title">
                        <Icon type="ios-videocam"></Icon>
                        视频库管理
                    </p>
                    <Row>
                        <label for="source">来源</label>
                        <Select v-model="source" id="source" style="width:200px">
                            <Option v-for="(name, id) in sourceList" :value="id" :key="id">{{ name }}</Option>
                        </Select>
                        <label for="type">上传者</label>
                        <Select v-model="type" id="type" style="width:200px" @on-change='change_type'>
                            <Option v-for="(name,id) in typeList" :key="id" :value="id">{{name}}</Option>
                        </Select>
                        <Select v-model="uploder_id" id="uploader_id" style="width:200px">
                            <Option v-for="(center_name,id) in uploderList" :value="id" :key="id">{{center_name}}
                            </Option>
                        </Select>
                        创建时间：
                        <DatePicker type="date" @on-change="getStartTime" placeholder="选择日期" style="    width: 200px"  v-model="throthe"></DatePicker>
                        <span>至</span>
                        <DatePicker type="date" @on-change="getEndTime" placeholder="选择日期" style="width: 200px"></DatePicker>

                        <label for="title">标题</label>
                        <Input v-model="title" id="title" placeholder="" style="width:200px;"/>

                        <Button @click="Search" type="primary" icon="search">搜索</Button>
                        <Button @click="Empty" type="ghost">清空</Button>
                    </Row>
                    <Row style="margin-top:15px;">
                        <Button @click="deleterAll" type="error">批量删除</Button>
                        <Button type="info" @click="videoAdd">添加视频</Button>
                    </Row>
                    <Row style="margin-top:15px;">
                        <Table highlight-row stripe border ref="selection" :columns="columns" :data="data"  @on-selection-change='selectionClick'></Table>
                    </Row>
                    <Page :current="current" :page-size="pageSize" :total="total" @on-change="handlePageChange"
                          show-elevator show-total></Page>
                </Card>
            </Col>
        </Row>
    </div>
</template>

<script>
    import axios from 'axios';
    import Cookies from 'js-cookie';

    export default {
        data: function () {
            let that = this;
            return {
                columns: [
                    {
                        type: 'selection',
                        width: 60,
                        align: 'center'
                    },
                    {
                        title: 'ID',
                        key: 'id'
                    },
                    {
                        title: '标题',
                        key: 'title'
                    },
                    {
                        title: '来源',
                        key: 'center_name'
                    },
                    {
                        title: '上传时间',
                        key: 'create_time',
                        render: function (h, params) {
                            return h('span', {}, that.dateFormat(params.row.create_time));
                        }
                    },
                    {
                        title: '上传用户',
                        key: 'uploder'
                    },
                    {
                        title: '状态',
                        key: 'state'
                    },
                    {
                        title: 'Action',
                        key: 'action',
                        width: 150,
                        align: 'center',
                        render: (h, params) => {
                            let that = this;
                            // 创建者是本馆用户以及本馆后台可编辑删除  其他查看
                            if (params.row.creator_center_id.toString() === this.cid) {
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
                                                that.$router.push({name: 'video_edit', params: {video_id: params.row.id}});
                                                // this.show(params.index)
                                            }
                                        }
                                    }, '编辑'),
                                    h('Poptip',
                                        {
                                            props: {
                                                confirm: true,
                                                title: '确定要删除吗！'
                                            },
                                            on: {
                                                'on-ok': function () {
                                                    that.delvideo(params.row.id);
                                                }
                                            }
                                        },
                                        [h('Button', {
                                            props: {
                                                type: 'error',
                                                size: 'small'
                                            }
                                        }, '删除')]
                                    )
                                ]);
                            } else {
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
                                                that.$router.push({name: 'video_view', params: {video_id: params.row.id}});
                                            }
                                        }
                                    }, '查看')
                                ]);
                            }
                        }
                    }
                ],
                data: [],
                source: 0,
                uploder_id: 0,
                start_time: 0,
                end_time: 0,
                startTime: 0,
                endTime: 0,
                type: 0,
                title: '',
                ids: [],
                current: 1,
                pageSize: 10,
                total: 0,
                typeList: {
                    1: '后台',
                    2: '老师'
                },
                sourceList: {},
                uploderList: {},
                dataList: {},
                cid: 0
            };
        },
        created: function () {
            this.cid = Cookies.get('center_id');
            // this.cid = '14';
            this.getvideoList();
            this.getCenterList(0);
        },
        methods: {
            getvideoList: function () {
                var that = this;
                console.log(11);
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {
                        params:
                            {
                                api: '/v1/video/index',
                                user_type: 3,
                                type: this.type,
                                page: this.current,
                                len: this.pageSize,
                                source: this.source,
                                uploder_id: this.uploder_id,
                                start_time: this.start_time,
                                end_time: this.end_time,
                                title: this.title
                            }
                    }
                ).then(function (response) {
                    let dat = response.data;
                    that.data = dat.data.list;
                    that.total = dat.data.num;
                }).catch(function (err) {
                    console.log(err.stack);
                });
            },
            getCenterList: function (type) {
                var that = this;
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
                        if (type === 0) {
                            that.sourceList = that.array_map(response.data, 'id', 'center_name');
                        } else {
                            that.uploderList = that.array_map(response.data, 'id', 'center_name');
                        }
                    } else {
                        that.sourceList = [];
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
                    if (response.data.status === 1) {
                        console.log(response.data.data);
                        that.uploderList = that.array_map(response.data.data.list, 'id', 'nick_name');
                    } else {
                        that.uploderList = {};
                    }
                }).catch(function (err) {
                    console.log(err.stack);
                });
            },
            handlePageChange: function (cur) {
                this.current = cur;
                this.getvideoList();
            },
            Search: function () {
                this.getvideoList();
            },
            Empty: function () {
                this.type = 0;
                this.source = 0;
                this.startTime = 0;
                this.endTime = 0;
                this.res='' ; 清空日期
                this.uploder_id = 0;
                this.title = '';
                this.getvideoList();
            },
            change_type: function () {
                if (this.type === '1') {
                    // 后台
                    this.getCenterList(1);
                } else {
                    this.getTeacherList(1);
                }
            },
            delvideo: function (id) {
                let that = this;
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {
                        params:
                            {
                                api: '/v1/video/delete',
                                user_type: 3,
                                id: id,
                                ids: this.ids
                            }
                    }
                ).then(function (response) {
                    if (response.data.status === 1) {
                        that.$Message.success('删除成功');
                        that.getvideoList();
                    } else {
                        that.$Message.error('删除失败');
                    }
                }).catch(function (err) {
                    console.log(err.stack);
                });
            },
            deleterAll: function () {
                console.log(this.ids);
                if (this.ids.length > 0) {
                    this.delvideo();
                    this.ids = [];
                } else {
                    this.$Message.info('请至少选择一项');
                }
            },
            selectionClick: function (arr) {
                var that = this;
                var ids = [];
                for (var i = 0; i < arr.length; i++) {
                    ids.push(arr[i]['id']);
                }
                that.ids = ids;
            },
            getStartTime: function (startTime) {
                this.startTime = startTime;
                this.start_time = this.timeToTimestamp(startTime);
            },
            getEndTime: function (endTime) {
                this.endTime = endTime;
                this.end_time = this.timeToTimestamp(endTime);
            },
            timeToTimestamp: function (time) {
                return new Date(time).getTime() / 1000;
            },
            videoAdd: function () {
                this.$router.push({name: 'video_add'});
            }
        }
    };
</script>
