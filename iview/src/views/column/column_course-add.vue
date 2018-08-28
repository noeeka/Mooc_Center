<style lang="less">
    @import '../../styles/common.less';
    @import 'column_course-add.less';
</style>

<template>
    <div>
        <Row :gutter="10">
            <Col>
                <Card>
                    <div slot="title">
                        <p style="width: auto;">
                            <Icon type="ios-paper-outline"></Icon>
                            栏目课程管理
                        </p>
                        <p style="width: auto; float: right;height:25px;">
                            <Button type="primary" size="small" @click="handleBack">返回</Button>
                        </p>
                        <div style="clear: both"></div>
                    </div>
                    <Row style="margin-top:15px;">
                        <Transfer
                                :data="data3"
                                :target-keys="targetKeys3"
                                :list-style="listStyle"
                                :title="['栏目课程', '所有课程']"
                                :render-format="render3"
                                :operations="['To left','To right']"
                                filterable
                                @on-change="handleChange3">
                            <div :style="{float: 'right', margin: '5px'}">
                                <Button type="ghost" size="small" @click="saveData">保存</Button>
                                <Button type="ghost" size="small" @click="refreshData">重置</Button>
                            </div>
                        </Transfer>
                    </Row>
                </Card>
            </Col>
        </Row>
    </div>

</template>

<script>
    import axios from 'axios';

    export default {
        data () {
            return {
                data3: this.getAllCourse(),
                targetKeys3: this.getColumnCourse(),
                newTargetKeys: [],
                course_ids: [],
                listStyle: {
                    width: '500px',
                    height: '550px'
                }
            };
        },
        methods: {
            request: function (params, callback1) {
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {params: params}
                ).then(callback1).catch();
            },
            getAllCourse () {
                let id = this.$route.params.id;
                let that = this;
                this.request(
                    {
                        api: '/v1/column/column_muke',
                        user_type: 3,
                        id: id
                    },
                    function (response) {
                        let res = response.data;
                        if (res.status === 1) {
                            if (res.data.all_mk.length !== 0) {
                                that.data3 = res.data.all_mk.map(function (v) {
                                    return {
                                        key: v.course_id.toString(),
                                        label: v.course_title,
                                        description: ''
                                    };
                                });
                                console.log(that.data3);
                            } else {
                                that.data3 = [];
                            }
                        }
                    }
                );
            },
            getColumnCourse () {
                let id = this.$route.params.id;
                let that = this;
                this.request(
                    {
                        api: '/v1/column/column_muke',
                        user_type: 3,
                        id: id
                    },
                    function (response) {
                        let res = response.data;
                        if (res.status === 1) {
                            if (res.data.col_mk.length !== 0) {
                                that.targetKeys3 = res.data.col_mk.map(function (v) {
                                    return v.course_id.toString();
                                });
                            } else {
                                that.targetKeys3 = [];
                            }
                        }
                    }
                );
            },
            handleChange3 (newTargetKeys, direction, moveKeys) {
                // this.targetData = this.targetKeys3;
                console.log(this.newTargetKeys);
                console.log(direction);
                console.log(moveKeys);
                this.targetKeys3 = newTargetKeys;
                console.log(this.targetKeys3);
                // this.newTargetKeys = newTargetKeys.map(function (v) {
                //     return Number(v);
                // });
            },
            render3 (item) {
                return item.label;
            },
            saveData () {
                let that = this;
                this.course_ids = this.targetKeys3.map(function (v) {
                    return Number(v);
                });
                this.request(
                    {
                        api: '/v1/column/updateRela',
                        user_type: 3,
                        course_ids: this.course_ids,
                        id: this.$route.params.id
                    }, function (response) {
                        let res = response.data;
                        if (res.status === 1) {
                            that.$Message.success(res.msg);
                        } else {
                            that.$Message.error(res.msg);
                        }
                    });
            },
            refreshData () {
                this.data3 = this.getAllCourse();
                this.targetKeys3 = this.getColumnCourse();
            },
            handleBack: function () {
                this.$router.go(-1);
            }
        }
    };
</script>
