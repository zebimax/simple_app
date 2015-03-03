<?php
return array(
    'solarium' => array(
        'endpoint' => array(
            'localhost' => array(
                'host' => '127.0.0.1',
                'port' => 8983,
                'path' => '/solr/dod',
            )
        )
    ),
    'mysql' => array(
        'log_queries' => false,
        'log_file' => '/home/users/deonoftp/data/logs/dberror.log',
        'host' => 'localhost',
        'port' => 3601,
        'user' => 'root',
        'password' => '',
        'db' => 'dod'
    ),
    'assembla_tickets' => array(
        'update' => array(
            /**
             * example of update tickets configs
             */
            'duplicate_tickets_to_invalid' => array(
                /**
                 * Tickets founded by conditions for each key will be affected by actions configured in updates section for corresponding key
                 *  see conditions config example
                 */
                'conditions' => getAllErrorTicketsConditionsConfig(),
                /**
                 *  see updates config example
                 */
                'updates' => getAllErrorTicketsUpdatesConfig(),
            )
        ),
        'get_info' => array(
            'time_of_tickets_solr_connect_error' => array(
                'space' => 'dod-mobile',
                'file_name' => MVC_APP_PATH . DIRECTORY_SEPARATOR . 'files/csv/assembla/time_of_tickets_solr_connect_error.csv',
                'range' => function() {
                    $range = array(
                        7011, 7206, 7208, 7216, 7217, 7218, 7221, 7222, 7223, 7238, 7239, 7240, 7241, 7242, 7246,
                        7247, 7248, 7249, 7250, 7252, 7253, 7254, 7255, 7256, 7257, 7258, 7262, 7263, 7264, 7265,
                        7266, 7267, 7268, 7269, 7274, 7275, 7276, 7277, 7278, 7279, 7292, 7293, 7294, 7295, 7298,
                        7300, 7301, 7320, 7321, 7322, 7323, 7324, 7325, 7329, 7330, 7331, 7332, 7333, 7334, 7335,
                        7337, 7361, 7362, 7363, 7364, 7365, 7366, 7368, 7373, 7374, 7375, 7376, 7377, 7378, 7419,
                        7420, 7422, 7423, 7424, 7425, 7426, 7427, 7428, 7429, 7430, 7431, 7432, 7433, 7434, 7435,
                        7436, 7442, 7443, 7444, 7445, 7446, 7447, 7448, 7449, 7450, 7451, 7452, 7453, 7463, 7464,
                        7465, 7466, 7467, 7468, 7469, 7470, 7471, 7472, 7473, 7474, 7475, 7476, 7477, 7478, 7479,
                        7488, 7489, 7490, 7540, 7541, 7542, 7543, 7544, 7545, 7546, 7547, 7548, 7549, 7576, 7577,
                        7578, 7579, 7580, 7581, 7605, 7606, 7607, 7608, 7609, 7622, 7623, 7624, 7625, 7631, 7632,
                        7633, 7634, 7635, 7664, 7665, 7666, 7669, 7670, 7671, 7672, 7673, 7674, 7710, 7711, 7713,
                        7714, 7715, 7716, 7717, 7718, 7719, 7725, 7726, 7727, 7735, 7737, 7738, 7739, 7740, 7741,
                        7742, 7743, 7744, 7745, 7937, 7962, 7964, 7965, 7966, 7969, 7970, 7977, 7978, 7980, 7981,
                        7982, 7983, 7984, 7985, 7993, 8013, 8014, 8015, 8018, 8019, 8021, 8022, 8023, 8024, 8026,
                        8040, 8041, 8042, 8044, 8045, 8046, 8047, 8048, 8049, 8050, 8079, 8085, 8088, 8092, 8093,
                        7299, 8095, 7207, 7304, 7306, 7308, 7309, 7310, 7312, 7318, 7326, 7336, 7338, 7341, 7342,
                        7346, 7352, 7367, 7380, 7383, 7389, 7390, 7421, 7440, 7441, 7454, 7460, 7461, 7494, 7505,
                        7508, 7514, 7519, 7531, 7536, 7552, 7554, 7557, 7558, 7560, 7572, 7582, 7626, 7638, 7649,
                        7668, 7685, 7686, 7695, 7697, 7700, 7703, 7721, 7723, 7728, 7730, 7731, 7751, 7756, 7760,
                        7762, 7771, 7781, 7936, 7972, 7986, 7988, 7998, 8062, 8063, 8065, 8071, 8089, 8091, 7244,
                        7291, 7316, 7350, 7355, 7356, 7359, 7379, 7384, 7402, 7403, 7407, 7409, 7411, 7412, 7438,
                        7455, 7459, 7499, 7501, 7502, 7512, 7516, 7533, 7537, 7555, 7559, 7565, 7575, 7594, 7597,
                        7613, 7621, 7640, 7652, 7655, 7656, 7658, 7661, 7677, 7681, 7707, 7733, 7772, 7776, 7784,
                        7788, 7967, 8072, 7213, 7220, 7227, 7236, 7260, 7271, 7281, 7286, 7289, 7290, 7303, 7305, 7307, 7311, 7313, 7314, 7315, 7317, 7319, 7327, 7340, 7343, 7344, 7345, 7353, 7354, 7357,
                        7360, 7369, 7370, 7372, 7382, 7385, 7386, 7387, 7388, 7391, 7392, 7393, 7399, 7400, 7401, 7404, 7408, 7413, 7417, 7418, 7437, 7439, 7456, 7457, 7462, 7480, 7483, 7484, 7485, 7491,
                        7496, 7500, 7503, 7504, 7510, 7511, 7518, 7521, 7522, 7526, 7532, 7535, 7550, 7556, 7562, 7563, 7566, 7568, 7569, 7573, 7584, 7585, 7586, 7587, 7592, 7595, 7596, 7600, 7602, 7610,
                        7611, 7614, 7616, 7617, 7618, 7619, 7627, 7628, 7630, 7637, 7641, 7642, 7643, 7644, 7645, 7646, 7647, 7650, 7651, 7653, 7654, 7657, 7662, 7675, 7676, 7680, 7682, 7683, 7684, 7689,
                        7691, 7692, 7693, 7698, 7699, 7701, 7702, 7706, 7708, 7712, 7720, 7722, 7732, 7734, 7736, 7746, 7747, 7750, 7754, 7758, 7759, 7761, 7764, 7765, 7766, 7768, 7769, 7773, 7774, 7775,
                        7777, 7779, 7782, 7785, 7786, 7787, 7792, 7935, 7938, 7971, 7990, 7997, 7999, 8027, 8035, 8064, 8084, 8087, 7211, 7214, 7225, 7229, 7230, 7231, 7232, 7233, 7235, 7245, 7272, 7273,
                        7282, 7285, 7288, 7296, 7297, 7302, 7328, 7339, 7347, 7348, 7349, 7351, 7358, 7371, 7381, 7394, 7395, 7396, 7397, 7398, 7405, 7406, 7410, 7414, 7415, 7416, 7458, 7481, 7482, 7486,
                        7487, 7492, 7493, 7495, 7497, 7498, 7506, 7507, 7509, 7513, 7515, 7517, 7520, 7523, 7524, 7525, 7527, 7528, 7529, 7530, 7534, 7538, 7539, 7551, 7553, 7561, 7564, 7567, 7570, 7571,
                        7574, 7583, 7588, 7589, 7590, 7591, 7593, 7598, 7599, 7601, 7603, 7604, 7612, 7615, 7620, 7629, 7636, 7639, 7648, 7659, 7660, 7663, 7667, 7678, 7679, 7687, 7688, 7690, 7694, 7696,
                        7704, 7705, 7709, 7724, 7729, 7748, 7749, 7752, 7753, 7755, 7757, 7763, 7767, 7770, 7778, 7780, 7783, 7789, 7790, 7791, 7934, 7975, 7979, 7987, 7996, 8009, 8010, 8017, 8036, 8043,
                        8090, 8097, 8098, 7793, 7794, 7795, 7796, 7797, 7798, 7799, 7800, 7801, 7802, 7803, 7804, 7805, 7806, 7807, 7808, 7809, 7810, 7811, 7812, 7813, 7814, 7815, 7816, 7817, 7818, 7819,
                        7820, 7821, 7822, 7823, 7824, 7825, 7826, 7827, 7828, 7829, 7830, 7831, 7832, 7833, 7834, 7835, 7836, 7837, 7838, 7839, 7840, 7841, 7842, 7843, 7844, 7845, 7846, 7847, 7848, 7849,
                        7850, 7851, 7852, 7853, 7854, 7855, 7856, 7857, 7858, 7859, 7860, 7861, 7862, 7863, 7864, 7865, 7866, 7867, 7868, 7912, 7913, 7914, 7917, 7918, 7919, 7920, 7921, 7922, 7923, 7924,
                        7925, 7926, 7927, 7928, 7929, 7930, 7931, 7932, 7933, 7869, 7870, 7871, 7872, 7873, 7874, 7875, 7876, 7877, 7878, 7879, 7880, 7881, 7882, 7883, 7884, 7885, 7886, 7887, 7888, 7889,
                        7890, 7891, 7892, 7893, 7894, 7895, 7896, 7897, 7898, 7899, 7900, 7901, 7902, 7903, 7904, 7905, 7906, 7907, 7908, 7909, 7910, 7915, 7916, 7968, 7989, 7991, 7992, 7994, 7995, 8011,
                        8016, 8020, 8028, 8032, 8033, 8096
                    );
                    sort($range);
                    return $range;
                },
                'fields' => array(
                    'number' => 'number',
                    'created' => 'created_on',
                    'message' => function($ticket) {
                        $start = strpos($ticket['description'], '<pre><code>');
                        return str_replace(array('<pre><code>', '</pre></code>'), array('', ''), substr(
                            $ticket['description'],
                            $start,
                            strpos($ticket['description'],  '</code></pre>') - $start
                        ));
                    }
                )
            ),
        )
    )
);

function getAllErrorTicketsConditionsConfig()
{
    return array(
        /**
         * conditions config example. You need set space of tickets, range(just array of ticket numbers)
         * for each filter type (eq|like) key (field of ticket) value(to compare with tickets field value)
         * eq means tickets.field.value == filter.value
         * like means strpos(tickets.field.value, filter.value) !== false
         */
        7011 => array(
            'space' => 'dod-mobile',
            'range' => range(8109, 8110),
            'filters' => array(
                array('key' => 'summary', 'type' => 'eq', 'value' => 'An Error Occurred!'),
                array('key' => 'status', 'type' => 'eq', 'value' => 'New'),
                array(
                    'key' => 'description',
                    'type' => 'like',
                    'value' => 'Solr HTTP error: HTTP request failed, connect() timed out!'
                ),
                array(
                    'key' => 'description',
                    'type' => 'like',
                    'value' => 'm.deonlinedrogist.nl/vendor/solarium/solarium/library/Solarium/Core/Client/Adapter/Curl.php line 235'
                )
            )
        ),
        7013 => array(
            'space' => 'dod-mobile',
            'range' => range(8109, 8110),
            'filters' => array(
                array('key' => 'summary', 'type' => 'eq', 'value' => 'An Error Occurred!'),
                array('key' => 'status', 'type' => 'eq', 'value' => 'New'),
                array(
                    'key' => 'description',
                    'type' => 'like',
                    'value' => 'Uncaught PHP Exception LogicException: "The controller must return a response (null given). Did you forget to add a return statement somewhere in your controller?'
                ),
                array(
                    'key' => 'description',
                    'type' => 'like',
                    'value' => '"url":"/account/login_check"'
                )
            )
        ),
        7020 => array(
            'space' => 'dod-mobile',
            'range' => range(8109, 8110),
            'filters' => array(
                array('key' => 'summary', 'type' => 'eq', 'value' => 'An Error Occurred!'),
                array('key' => 'status', 'type' => 'eq', 'value' => 'New'),
                array(
                    'key' => 'description',
                    'type' => 'like',
                    'value' => 'An exception has been thrown during the rendering of a template'
                ),
                array(
                    'key' => 'description',
                    'type' => 'like',
                    'value' => 'Failed to create'
                )
            )
        ),
        7022 => array(
            'space' => 'dod-mobile',
            'range' => range(8109, 8110),
            'filters' => array(
                array('key' => 'summary', 'type' => 'eq', 'value' => 'An Error Occurred!'),
                array('key' => 'status', 'type' => 'eq', 'value' => 'New'),
                array(
                    'key' => 'description',
                    'type' => 'like',
                    'value' => 'java.lang.NullPointerException'
                ),
                array(
                    'key' => 'description',
                    'type' => 'like',
                    'value' => 'Solr HTTP error: OK'
                ),
                array(
                    'key' => 'description',
                    'type' => 'like',
                    'value' => '"url":"/search/autocomplete/?term'
                )
            )
        ),
        7176 => array(
            'space' => 'dod-mobile',
            'range' => range(8109, 8110),
            'filters' => array(
                array('key' => 'summary', 'type' => 'eq', 'value' => 'An Error Occurred!'),
                array('key' => 'status', 'type' => 'eq', 'value' => 'New'),
                array(
                    'key' => 'description',
                    'type' => 'like',
                    'value' => 'solr.search.SyntaxError: Cannot parse \'category_facet'
                ),
                array(
                    'key' => 'description',
                    'type' => 'like',
                    'value' => '"url":"/search/?categories'
                )
            )
        ),
        7869 => array(
            'space' => 'dod-mobile',
            'range' => range(8109, 8110),
            'filters' => array(
                array('key' => 'summary', 'type' => 'eq', 'value' => 'An Error Occurred!'),
                array('key' => 'status', 'type' => 'eq', 'value' => 'New'),
                array(
                    'key' => 'description',
                    'type' => 'like',
                    'value' => 'Solr HTTP error: HTTP request failed, Operation timed out after'
                )
            )
        ),
    );
}

function getAllErrorTicketsUpdatesConfig()
{
    return array(
        7011 => getDefaultInvalidUpdates(7011),
        7013 => getDefaultInvalidUpdates(7013),
        7020 => getDefaultInvalidUpdates(7020),
        7022 => getDefaultInvalidUpdates(7022),
        7176 => getDefaultInvalidUpdates(7176),
        7869 => getDefaultInvalidUpdates(7869)
    );
}

/**
 * @param $label
 * @return array
 */
function getDefaultInvalidUpdates($label)
{
    return array(
        'put' => array(
            'status' => 'Invalid',
            'state' => 0
        ),
        'post' => array('comment' => 'duplicate of #' . $label)
    );
}


function getErrorTicketsConditionsConfig($label)
{
    $config = array();
    $allConfig = getAllErrorTicketsConditionsConfig();
    if (isset($allConfig[$label])) {
        $config = $allConfig[$label];
    }
    return $config;
}

function getErrorTicketsUpdatesConfig($label)
{
    $config = array();
    $allConfig = getAllErrorTicketsUpdatesConfig();
    if (isset($allConfig[$label])) {
        $config = $allConfig[$label];
    }
    return $config;
}