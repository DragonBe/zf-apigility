<?php
return [
    'router' => [
        'routes' => [
            'zfdemo.rest.user' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/user[/:user_id]',
                    'defaults' => [
                        'controller' => 'zfdemo\\V1\\Rest\\User\\Controller',
                    ],
                ],
            ],
        ],
    ],
    'zf-versioning' => [
        'uri' => [
            0 => 'zfdemo.rest.user',
        ],
    ],
    'service_manager' => [
        'invokables' => [
            'zfdemo\\V1\\Rest\\User\\UserResource' => 'zfdemo\\V1\\Rest\\User\\UserResource',
        ],
    ],
    'zf-rest' => [
        'zfdemo\\V1\\Rest\\User\\Controller' => [
            'listener' => 'zfdemo\\V1\\Rest\\User\\UserResource',
            'route_name' => 'zfdemo.rest.user',
            'route_identifier_name' => 'user_id',
            'collection_name' => 'user',
            'entity_http_methods' => [
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ],
            'collection_http_methods' => [
                0 => 'GET',
                1 => 'POST',
            ],
            'collection_query_whitelist' => [],
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'zfdemo\\V1\\Rest\\User\\UserEntity',
            'collection_class' => 'zfdemo\\V1\\Rest\\User\\UserCollection',
            'service_name' => 'user',
        ],
    ],
    'zf-content-negotiation' => [
        'controllers' => [
            'zfdemo\\V1\\Rest\\User\\Controller' => 'HalJson',
        ],
        'accept_whitelist' => [
            'zfdemo\\V1\\Rest\\User\\Controller' => [
                0 => 'application/vnd.zfdemo.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
        ],
        'content_type_whitelist' => [
            'zfdemo\\V1\\Rest\\User\\Controller' => [
                0 => 'application/vnd.zfdemo.v1+json',
                1 => 'application/json',
            ],
        ],
    ],
    'zf-hal' => [
        'metadata_map' => [
            'zfdemo\\V1\\Rest\\User\\UserEntity' => [
                'entity_identifier_name' => 'id',
                'route_name' => 'zfdemo.rest.user',
                'route_identifier_name' => 'user_id',
                'hydrator' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
            ],
            'zfdemo\\V1\\Rest\\User\\UserCollection' => [
                'entity_identifier_name' => 'id',
                'route_name' => 'zfdemo.rest.user',
                'route_identifier_name' => 'user_id',
                'is_collection' => true,
            ],
        ],
    ],
];
