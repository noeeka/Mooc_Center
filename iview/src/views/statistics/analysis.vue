<style lang="less">
    @import './index.less';
</style>
<template>
    <div class="layout">
        <Layout>

            <!--<Menu mode="horizontal" theme="dark" active-name="1"> -->
            <div id="app" style="background-color: #1C2438;height: 45px; padding: 15px;">
                <RadioGroup style="color: #2db7f5" @on-change="select_date">
                    <Radio label="1">近24小时</Radio>
                    <Radio label="2">近7天</Radio>
                    <Radio label="3">近1个月</Radio>
                    <Radio label="4">近3个月</Radio>
                    <Radio label="select">
                        <div id="timeRange" style="display: none;">
                            从
                            <DatePicker type="date" placeholder="Select date" style="width: 200px"
                                        @on-change="selectStartDate"></DatePicker>
                            <input type="hidden" id="start_date"/>
                            到
                            <DatePicker type="date" placeholder="Select date" style="width: 200px"
                                        @on-change="selectEndDate"></DatePicker>
                            <input type="hidden" id="end_date"/>
                            <Button type="primary" @click="showDateRange">查询</Button>
                        </div>
                    </Radio>
                </RadioGroup>

            </div>
            <div>
                <Row :gutter="10">
                    <Col span="24">
                        <Card>
                            <p slot="title">
                                <Icon type="ios-book-outline"></Icon>
                                慕课库管理
                            </p>
                            <Row id="activate" style="height:600px;width: 100%"></Row>


                            <Tabs value="name1" @on-click="setCategory">
                                <TabPane label="课程报名" name="1"></TabPane>
                                <TabPane label="评价" name="2"></TabPane>
                                <TabPane label="讨论" name="3"></TabPane>
                                <TabPane label="笔记" name="6"></TabPane>
                            </Tabs>

                            <Row id="analysisresult" style="height:600px;width: 100%"></Row>
                        </Card>
                    </Col>
                </Row>
            </div>
        </Layout>
    </div>
</template>
<script>
    import * as echarts from 'echarts';
    import jq from 'jquery';

    export default {
        created: function () {

        },
        data: function () {
            return {
                timerange: '1'

            };
        },
        created() {
            this.select_date(1);
            this.setCategory(1);
        },
        methods: {
            select_date: function (message) {
                if (message == "select") {
                    jq("#app").css("padding", 5);
                    jq("#timeRange").css("display", "inline-block")
                    jq("#timeRange").show()
                } else {

                    jq("#timeRange").hide()
                }
                this.axios_request(
                    {
                        api: '/v1/chen/getActivate',
                        date_range: arguments[0]
                    },
                    function (res) {
                        var activate_chart = echarts.init(document.getElementById('activate'));
                        var activate_option = {
                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'shadow'
                                }
                            },
                            legend: {
                                data: ['登录', '评价', '课程访问', '讨论', '课程新增', '课程收藏', '加入课程']
                            },
                            toolbox: {
                                show: true,
                                orient: 'vertical',
                                left: 'right',
                                top: 'center',
                                feature: {
                                    mark: {show: true},
                                    dataView: {show: true, readOnly: false},
                                    magicType: {show: true, type: ['line', 'bar', 'stack', 'tiled']},
                                    restore: {show: true},
                                    saveAsImage: {show: true}
                                }
                            },
                            calculable: true,
                            xAxis: [
                                {
                                    type: 'category',
                                    axisTick: {show: false},
                                    data: []
                                }
                            ],
                            yAxis: [
                                {
                                    type: 'value'
                                }
                            ],
                            series: []
                        };
                        activate_option.xAxis[0].data = res.data.data.axis;
                        activate_option.series = res.data.data.seriers;
                        activate_chart.setOption(activate_option);
                    }
                );
            },
            selectStartDate: function (message) {
                jq("#start_date").val(message);
            },
            selectEndDate: function (message) {
                jq("#end_date").val(message);
            },
            showDateRange: function () {
                this.axios_request(
                    {
                        api: '/v1/chen/getActivateByRange',
                        start: jq("#start_date").val(),
                        end:jq("#end_date").val()
                    },
                    function (res) {
                        var activate_chart = echarts.init(document.getElementById('activate'));
                        var activate_option = {
                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'shadow'
                                }
                            },
                            legend: {
                                data: ['登录', '评价', '课程访问', '讨论', '课程新增', '课程收藏', '加入课程']
                            },
                            toolbox: {
                                show: true,
                                orient: 'vertical',
                                left: 'right',
                                top: 'center',
                                feature: {
                                    mark: {show: true},
                                    dataView: {show: true, readOnly: false},
                                    magicType: {show: true, type: ['line', 'bar', 'stack', 'tiled']},
                                    restore: {show: true},
                                    saveAsImage: {show: true}
                                }
                            },
                            calculable: true,
                            xAxis: [
                                {
                                    type: 'category',
                                    axisTick: {show: false},
                                    data: []
                                }
                            ],
                            yAxis: [
                                {
                                    type: 'value'
                                }
                            ],
                            series: []
                        };
                        activate_option.xAxis[0].data = res.data.data.axis;
                        activate_option.series = res.data.data.seriers;
                        activate_chart.setOption(activate_option);
                    }
                );

            },

            setCategory: function (message) {
                this.axios_request({
                    api: '/v1/chen/getAnalysisResult',
                    type: message
                }, function (res) {
                    var analysisresult_chart = echarts.init(document.getElementById('analysisresult'));
                    var analysisresult_option = {
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'shadow'
                            }
                        },
                        grid: {
                            left: '3%',
                            right: '4%',
                            bottom: '3%',
                            containLabel: true
                        },
                        xAxis: {
                            type: 'value',
                            boundaryGap: [0, 0.01]
                        },
                        yAxis: {
                            type: 'category',
                            data: []
                        },
                        series: [
                            {
                                type: 'bar',
                                data: [18203, 23489, 29034, 104970, 131744, 630230]
                            }
                        ]
                    };

                    analysisresult_option.yAxis.data = res.data.data.yaxis;
                    analysisresult_option.series[0].data = res.data.data.xaxis;
                    analysisresult_chart.setOption(analysisresult_option);
                });
            }
        }
    };
</script>
