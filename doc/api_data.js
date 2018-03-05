define({ "api": [
  {
    "type": "post",
    "url": "/cross-challege/add-log",
    "title": "添加活动日志",
    "name": "_cross_challege_add_log",
    "group": "activity_task_____",
    "sampleRequest": [
      {
        "url": "url http://www.soultask.com:9005//cross-challege/add-log"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "uid",
            "description": "<p>用户id 【必传】</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "activity_id",
            "description": "<p>活动id 【必传】</p>"
          },
          {
            "group": "Parameter",
            "type": "unid",
            "optional": false,
            "field": "game_no",
            "description": "<p>游戏ID 【必传】</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "win_result",
            "description": "<p>输赢结果，是否一定要赢【比传】@1yes,0no</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "friends_num",
            "description": "<p>好友数量【必传】</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "user_level",
            "description": "<p>用户等级【必传】</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "own_open_room",
            "description": "<p>是否自己开房【必传】  自己开房@1y,0n,2不验证</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>数据唯一id</p>"
          },
          {
            "group": "Success 200",
            "type": "Number",
            "optional": false,
            "field": "activity_id",
            "description": "<p>活动id</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "成功的返回:",
          "content": "{\n\"code\": 1,\n \"msg\": \"添加任务进度成功\",\n \"data\": {\n     \"id\": \"2\",\n     \"uid\": \"1\",\n     \"activity_id\": \"304\",\n      \"challege_list\": \"1\",\n     \"achieve_list\": \"{\\\"1\\\":[9,0]}\",\n     \"gift_list\": \"{\\\"1\\\":500}\",\n      \"is_receive\": \"2\",\n      \"frequency\": \"1\",\n     \"current_frequency\": \"1\",\n     \"img_icon\": \"jinbi.png\",\n     \"add_time\": \"20170912\",\n      \"create_time\": \"1505192535\",\n     \"update_time\": \"1505192535\"\n     }\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "失败返回:",
          "content": "{\n\"code\": -238,\n\"msg\": \"该活动不存在该游戏关卡,无相关关卡条件\",\n\"data\": []\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./Controller/CrossChallege.php",
    "groupTitle": "activity_task_____"
  },
  {
    "type": "get",
    "url": "/cross-challege/receive-gift",
    "title": "获取该活动的用户参与进度",
    "name": "_cross_challege_receive_gift",
    "group": "activity_task_____",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "activity_id",
            "description": "<p>活动id 【必传】</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "uid",
            "description": "<p>用户id 【必传】</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "scene_id",
            "description": "<p>场景id 【必传】</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Number",
            "optional": false,
            "field": "code",
            "description": "<p>响应编码</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "msg",
            "description": "<p>信息注解</p>"
          },
          {
            "group": "Success 200",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>数据</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "成功的返回:",
          "content": "{\n\"code\": 1,\n\"msg\": \"领取活动任务奖励成功\",\n\"data\": []\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./Controller/CrossChallege.php",
    "groupTitle": "activity_task_____"
  },
  {
    "type": "get",
    "url": "/cross-challege/show-activity-gifts",
    "title": "获取活动相关奖励",
    "name": "_cross_challege_show_activity_gifts",
    "group": "activity_task_____",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "activity_id",
            "description": "<p>活动id 【必传】</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Number",
            "optional": false,
            "field": "code",
            "description": "<p>响应编码</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "msg",
            "description": "<p>信息注解</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "成功的返回:",
          "content": "{\n\"code\": 1,\n \"msg\": \"获取成功\",\n \"data\": [\n{\n \"name\": \"金币\",\n \"num\": 5000,\n\"id\": 1\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "失败返回:",
          "content": "{\n\"code\": -238,\n\"msg\": \"该活动不存在该游戏关卡,无相关关卡条件\",\n\"data\": []\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./Controller/CrossChallege.php",
    "groupTitle": "activity_task_____"
  },
  {
    "type": "get",
    "url": "/cross-challege/show-activity-status",
    "title": "获取活动领取奖励状态",
    "name": "_cross_challege_show_activity_status",
    "group": "activity_task_____",
    "sampleRequest": [
      {
        "url": "url http://local.poker.com:9508/first-login/gift-daily-list?uid=1"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "uid",
            "description": "<p>用户id 【必传】</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "activity_id",
            "description": "<p>活动id 【必传】</p>"
          },
          {
            "group": "Parameter",
            "type": "unid",
            "optional": false,
            "field": "game_no",
            "description": "<p>游戏ID 【必传】</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "win_result",
            "description": "<p>输赢结果，是否一定要赢【比传】@1yes,0no</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "friends_num",
            "description": "<p>好友数量【必传】</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "user_level",
            "description": "<p>用户等级【必传】</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "own_open_room",
            "description": "<p>是否自己开房【必传】  自己开房@1y,0n,2不验证</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>数据唯一id</p>"
          },
          {
            "group": "Success 200",
            "type": "Number",
            "optional": false,
            "field": "activity_id",
            "description": "<p>活动id</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "成功的返回:",
          "content": "{\n\"code\": 1,\n\"msg\": \"获取成功\",\n\"data\": [\n {\n \"activity_id\": \"304\",\n \"is_receive\": 0,\n\"img_icon\": \"jinbi.png\",\n\"gift_list\": [\n{\n\"name\": \"金币\",\n\"num\": 500,\n \"id\": 1\n  }\n ]\n},\n{\n\"activity_id\": \"305\",\n\"is_receive\": 0,\n\"img_icon\": \"jinbi.png\",\n\"gift_list\": [\n{\n\"name\": \"金币\",\n \"num\": 5000,\n \"id\": 1\n}\n ]\n },\n  {\n \"activity_id\": \"306\",\n\"is_receive\": 0,\n\"img_icon\": \"fagnka.png\",\n\"gift_list\": [\n{\n\"name\": \"房卡 \",\n \"num\": 10,\n \"id\": 3\n }\n]\n},\n {\n \"activity_id\": \"309\",\n  \"is_receive\": 0,\n \"img_icon\": \"fagnka.png\",\n \"gift_list\": [\n{\n\"name\": \"房卡 \",\n\"num\": 2,\n\"id\": 3\n}\n]\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./Controller/CrossChallege.php",
    "groupTitle": "activity_task_____"
  },
  {
    "type": "get",
    "url": "/cross-challege/show-join-status",
    "title": "获取该活动的用户参与进度",
    "name": "_cross_challege_show_join_status",
    "group": "activity_task_____",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "activity_id",
            "description": "<p>活动id 【必传】</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "uid",
            "description": "<p>用户id 【必传】</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "scene_id",
            "description": "<p>场景id 【必传】</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Number",
            "optional": false,
            "field": "code",
            "description": "<p>响应编码</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "msg",
            "description": "<p>信息注解</p>"
          },
          {
            "group": "Success 200",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>数据</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "成功的返回:",
          "content": "{\n\"code\": 1,\n \"msg\": \"获取活动参与的进度成功\",\n \"data\": [\n   {\n\"achieve_status\": true,\n \"achieve_num\": 0,\n \"total_num\": \"1\",\n \"challege_config_id\": \"2\"\n }\n ]\n }",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "./Controller/CrossChallege.php",
    "groupTitle": "activity_task_____"
  }
] });
