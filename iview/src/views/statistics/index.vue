<style lang="less">
    @import './index.less';
</style>

<template>
    <div>
        <Row :gutter="10">
            <Col span="24">
                <Card>
                    <p slot="title">
                        <Icon type="ios-book-outline"></Icon>
                        统计分析
                    </p>
                    <Row class="analysis">
                        <div class="circle comment text">评论数<span v-model="comment_num">{{comment_num}}</span></div>
                        <div class="circle course text">课程数<span v-model="course_num">{{course_num}}</span></div>
                        <div class="circle student text">学生数<span v-model="student_num">{{student_num}}</span></div>
                        <div class="circle teacher text">老师数<span v-model="teacher_num">{{teacher_num}}</span></div>
                        <div class="circle note text">笔记数<span v-model="note_num">{{note_num}}</span></div>
                        <div class="circle question text">问答数<span v-model="question_num">{{question_num}}</span></div>
                        <div class="Square">课程总览</div>
                    </Row>
                    <Row>
                        <p>
                            <Icon type="ios-book-outline"></Icon>
                            最受欢迎的课程top：按报名人数
                        </p>
                        <div id="popularity" style="width: 1800px;height: 450px; margin-top: 20px;margin-left: 20px;position: relative;left:-120px;"></div>
                    </Row>
                    <Row>
                        <p>
                            <Icon type="ios-book-outline"></Icon>
                            活跃度最高的的课程top：按笔记问答总数
                        </p>
                        <div id="activity" style="width: 1800px;height: 450px; margin-top: 20px;;margin-left: 20px;position: relative;left:-120px;"></div>
                    </Row>
                    <Row>
                        <p>
                            <Icon type="ios-book-outline"></Icon>
                            最受欢迎的老师top：按学生人数
                        </p>
                        <div id="teacher_popularity" style="width: 100%;height: 450px;"></div>
                    </Row>
                    <Row>
                        <p>
                            <Icon type="ios-book-outline"></Icon>
                            学生活跃度
                        </p>
                        <RadioGroup v-model="time_slot" @on-change="changeSlot">
                            <Radio label="1" >近24小时</Radio>
                            <Radio label="2">近一周</Radio>
                            <Radio label="3">近一个月</Radio>
                            <Radio label="4">近三个月</Radio>
                        </RadioGroup>
                        <div id="student_activity" style="width: 100%;height: 450px; margin-top: 20px;position: relative;left:-120px;"></div>
                    </Row>
                    <Row>
                        <Col span="8">
                            <p>
                                <Icon type="ios-book-outline"></Icon>
                                好评课程占比
                            </p>
                            <div id="praise" style="width: 500px;height: 450px; margin-top: 20px;"></div>
                        </Col>
                        <Col span="8">
                            <p>
                                <Icon type="ios-book-outline"></Icon>
                                参与讨论的学生比率
                            </p>
                            <div id="ratio" style="width: 500px;height: 450px; margin-top: 20px;"></div>
                        </Col>
                        <Col span="8">
                            <p>
                                <Icon type="ios-book-outline"></Icon>
                                终端设备使用比：手机端VS pc端
                            </p>
                            <div id="equipment" style="width: 500px;height: 450px; margin-top: 20px;"></div>
                        </Col>
                    </Row>
                </Card>
            </Col>
        </Row>
    </div>
</template>

<script>
    import * as echarts from 'echarts';

    export default {
        data () {
            return {
                comment_num: 0,
                course_num: 0,
                student_num: 0,
                teacher_num: 0,
                note_num: 0,
                question_num: 0,
                time_slot: '1'
            };
        },
        created () {
            this.getPandectNum();
            this.getPopularity();
            this.getActivity();
            this.getTeacherPop();
            this.getStuActivity();
            this.getPraise();
            this.getDiscuss();
            this.getEquipment();
        },
        methods: {
            getPandectNum: function () {
                let that = this;
                that.axios_request(
                    {
                        api: '/v1/statistics/pandect',
                        user_type: 3
                    },
                    function (res) {
                        res = res.data;
                        if (res.status === 1) {
                            let dat = res.data;
                            that.comment_num = dat.comment_num;
                            that.course_num = dat.course_num;
                            that.student_num = dat.student_num;
                            that.teacher_num = dat.teacher_num;
                            that.note_num = dat.note_num;
                            that.question_num = dat.que_ans_num;
                        }
                    }
                );
            },
            getPopularity: function () {
                this.axios_request(
                    {
                        api: '/v1/statistics/popularity',
                        user_type: 3
                    },
                    function (res) {
                        res = res.data;
                        if (res.status === 1) {
                            var popularityChart = echarts.init(document.getElementById('popularity'));
                            var option = {
                                color: ['#3398DB'],
                                tooltip: {
                                    trigger: 'axis',
                                    axisPointer: { // 坐标轴指示器，坐标轴触发有效
                                        type: 'shadow' // 默认为直线，可选为：'line' | 'shadow'
                                    }
                                },
                                xAxis: [
                                    {
                                        type: 'category',
                                        data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                                        axisTick: {
                                            alignWithLabel: true
                                        }
                                    }
                                ],
                                yAxis: [
                                    {
                                        type: 'value'
                                    }
                                ],
                                series: [
                                    {
                                        name: '受欢迎程度',
                                        type: 'bar',
                                        barWidth: '60%',
                                        data: [10, 52, 200, 334, 390, 330, 220]
                                    }
                                ]
                            };
                            option.xAxis[0].data = res.data.course_titles;
                            option.series[0].data = res.data.baoming_num;
                            popularityChart.setOption(option);
                        }
                    }
                );
            },
            getActivity: function () {
                this.axios_request(
                    {
                        api: '/v1/statistics/activity',
                        user_type: 3
                    },
                    function (res) {
                        res = res.data;
                        if (res.status === 1) {
                            var activityChart = echarts.init(document.getElementById('activity'));
                            var option1 = {
                                color: ['#3398DB'],
                                tooltip: {
                                    trigger: 'axis',
                                    axisPointer: { // 坐标轴指示器，坐标轴触发有效
                                        type: 'shadow' // 默认为直线，可选为：'line' | 'shadow'
                                    }
                                },
                                xAxis: [
                                    {
                                        type: 'category',
                                        data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                                        axisTick: {
                                            alignWithLabel: true
                                        }
                                    }
                                ],
                                yAxis: [
                                    {
                                        type: 'value'
                                    }
                                ],
                                series: [
                                    {
                                        name: '活跃度',
                                        type: 'bar',
                                        barWidth: '60%',
                                        data: [10, 52, 200, 334, 390, 330, 220]
                                    }
                                ]
                            };
                            option1.xAxis[0].data = res.data.courses;
                            option1.series[0].data = res.data.total;
                            activityChart.setOption(option1);
                        }
                    }
                );
            },
            getTeacherPop: function () {
                this.axios_request(
                    {
                        user_type: 3,
                        api: '/v1/statistics/teacherPop'
                    },
                    function (response) {
                        let res = response.data;
                        if (res.status === 1) {
                            let teacherChart = echarts.init(document.getElementById('teacher_popularity'));
                            let option3 = {
                                color: ['#3398DB'],
                                tooltip: {
                                    trigger: 'axis',
                                    axisPointer: { // 坐标轴指示器，坐标轴触发有效
                                        type: 'shadow' // 默认为直线，可选为：'line' | 'shadow'
                                    }
                                },
                                xAxis: [
                                    {
                                        type: 'category',
                                        data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                                        axisTick: {
                                            alignWithLabel: true
                                        }
                                    }
                                ],
                                yAxis: [
                                    {
                                        type: 'value'
                                    }
                                ],
                                series: [
                                    {
                                        name: '老师受欢迎程度',
                                        type: 'bar',
                                        barWidth: '60%',
                                        data: [10, 52, 200, 334, 390, 330, 220]
                                    }
                                ]
                            };
                            option3.xAxis[0].data = res.data.teachers;
                            option3.series[0].data = res.data.num;
                            teacherChart.setOption(option3);
                        }
                    }
                );
            },
            getStuActivity: function () {
                let that = this;
                that.axios_request(
                    {
                        user_type: 3,
                        api: '/v1/statistics/getStuActivity',
                        slot: this.time_solt
                    },
                    function (response) {
                        let res = response.data;
                        if (res.status === 1) {
                            let studentChart = echarts.init(document.getElementById('student_activity'));
                            let option4 = {
                                xAxis: {
                                    type: 'category',
                                    data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
                                },
                                yAxis: {
                                    type: 'value'
                                },
                                series: [{
                                    data: [820, 932, 901, 934, 1290, 1330, 1320],
                                    type: 'line'
                                }]
                            };
                            option4.xAxis.data = res.data.time_interval;
                            option4.series[0].data = res.data.data;
                            studentChart.setOption(option4);
                        }
                    });
            },
            changeSlot: function (solt) {
                this.time_solt = solt;
                this.getStuActivity();
            },
            getPraise: function () {
                this.axios_request(
                    {
                        user_type: 3,
                        api: '/v1/statistics/praise'
                    },
                    function (response) {
                        let res = response.data;
                        if (res.status === 1) {
                            let praiseChart = echarts.init(document.getElementById('praise'));
                            let option5 = {
                                tooltip: {
                                    trigger: 'item',
                                    formatter: '{a} <br/>{b}: {c} ({d}%)'
                                },
                                legend: {
                                    orient: 'vertical',
                                    x: 'left',
                                    data: ['好评', '差评']
                                },
                                series: [
                                    {
                                        name: '评价',
                                        type: 'pie',
                                        radius: ['50%', '70%'],
                                        avoidLabelOverlap: false,
                                        label: {
                                            normal: {
                                                show: false,
                                                position: 'center'
                                            },
                                            emphasis: {
                                                show: true,
                                                textStyle: {
                                                    fontSize: '30',
                                                    fontWeight: 'bold'
                                                }
                                            }
                                        },
                                        labelLine: {
                                            normal: {
                                                show: false
                                            }
                                        },
                                        data: [
                                            {value: 335, name: '好评'},
                                            {value: 310, name: '差评'}
                                        ]
                                    }
                                ]
                            };
                            option5.series[0].data[0].value = res.data.praise;
                            option5.series[0].data[1].value = res.data.nagative;
                            praiseChart.setOption(option5);
                        }
                    }
                );
            },
            getDiscuss: function () {
                this.axios_request(
                    {
                        user_type: 3,
                        api: '/v1/statistics/discuss'
                    },
                    function (response) {
                        let res = response.data;
                        if (res.status === 1) {
                            let ratioChart = echarts.init(document.getElementById('ratio'));
                            let option6 = {
                                tooltip: {
                                    trigger: 'item',
                                    formatter: '{a} <br/>{b}: {c} ({d}%)'
                                },
                                legend: {
                                    orient: 'vertical',
                                    x: 'left',
                                    data: ['参与讨论', '未参与讨论']
                                },
                                series: [
                                    {
                                        name: '讨论比率',
                                        type: 'pie',
                                        radius: ['50%', '70%'],
                                        avoidLabelOverlap: false,
                                        label: {
                                            normal: {
                                                show: false,
                                                position: 'center'
                                            },
                                            emphasis: {
                                                show: true,
                                                textStyle: {
                                                    fontSize: '30',
                                                    fontWeight: 'bold'
                                                }
                                            }
                                        },
                                        labelLine: {
                                            normal: {
                                                show: false
                                            }
                                        },
                                        data: [
                                            {value: 335, name: '参与讨论'},
                                            {value: 310, name: '未参与讨论'}
                                        ]
                                    }
                                ]
                            };
                            option6.series[0].data[0].value = res.data.discuss;
                            option6.series[0].data[1].value = res.data.nodiscuss;
                            ratioChart.setOption(option6);
                        }
                    }
                );
            },
            getEquipment: function () {
                this.axios_request(
                    {
                        user_type: 3,
                        api: '/v1/statistics/equipment'
                    },
                    function (response) {
                        let res = response.data;
                        if (res.status === 1) {
                            let equipment = echarts.init(document.getElementById('equipment'));
                            let option7 = {
                                tooltip: {
                                    trigger: 'item',
                                    formatter: '{a} <br/>{b}: {c} ({d}%)'
                                },
                                legend: {
                                    orient: 'vertical',
                                    x: 'left',
                                    data: ['pc端访问数量', 'wx端访问数量']
                                },
                                series: [
                                    {
                                        name: '访问来源',
                                        type: 'pie',
                                        radius: ['50%', '70%'],
                                        avoidLabelOverlap: false,
                                        label: {
                                            normal: {
                                                show: false,
                                                position: 'center'
                                            },
                                            emphasis: {
                                                show: true,
                                                textStyle: {
                                                    fontSize: '30',
                                                    fontWeight: 'bold'
                                                }
                                            }
                                        },
                                        labelLine: {
                                            normal: {
                                                show: false
                                            }
                                        },
                                        data: [
                                            {value: 335, name: 'pc端访问数量'},
                                            {value: 310, name: 'wx端访问数量'}
                                        ]
                                    }
                                ]
                            };
                            option7.series[0].data[0].value = res.data.pc_num;
                            option7.series[0].data[1].value = res.data.wx_num;
                            equipment.setOption(option7);
                        }
                    }
                );
            }

        },
        mounted: function () {

        }

    };
</script>
