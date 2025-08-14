# ChannelIO 통합 가이드

## 개요
이 프로젝트는 PHP 세션 정보를 활용하여 ChannelIO를 통합한 것입니다. 사용자의 로그인 상태, ID, 이름, 이메일 등의 정보를 자동으로 ChannelIO 프로필에 설정합니다.

## 파일 구조

### 1. `phpmodules/ChannelIO.php`
- ChannelIO 스크립트를 생성하는 PHP 함수
- 세션 정보를 읽어서 JavaScript 변수로 변환
- `generateChannelIOScript()` 함수를 제공

### 2. 통합된 파일들
- `login.php` - 로그인 페이지
- `dist_ios.php` - iOS 배포 페이지
- `dist_android.php` - Android 배포 페이지  
- `dist_domestic.php` - 국내 배포 메인 페이지

### 3. `test_channelio.html`
- ChannelIO 스크립트 테스트용 페이지
- 실제 PHP 환경 없이도 테스트 가능

## 사용법

### 기본 사용법
각 PHP 파일에서 다음과 같이 ChannelIO 스크립트를 삽입합니다:

```php
<!-- ChannelIO 스크립트 -->
<?php
require_once('./phpmodules/ChannelIO.php');  // 경로는 파일 위치에 따라 조정
echo generateChannelIOScript();
?>
```

### 세션 정보 활용
스크립트는 다음 세션 변수들을 자동으로 읽어옵니다:

- `$_SESSION['id']` - 일반 사용자 ID
- `$_SESSION['internal_id']` - 내부 사용자 ID (우선순위 높음)
- `$_SESSION['email']` - 사용자 이메일
- `$_SESSION['name']` - 사용자 이름
- `$_SESSION['token']` - 사용자 토큰

### 사용자 타입 구분
- **internal**: `$_SESSION['internal_id']`가 설정된 경우
- **client**: `$_SESSION['id']`만 설정된 경우
- **guest**: 세션 정보가 없는 경우

## ChannelIO 설정

### 플러그인 키
현재 설정된 플러그인 키: `a8998820-06d9-420b-964a-c3e072a1265f`

### 커스텀 값들
- `CUSTOM_VALUE_1`: 사용자 ID
- `CUSTOM_VALUE_2`: 사용자 타입 (internal/client/guest)
- `CUSTOM_VALUE_3`: 로그인 상태
- `CUSTOM_VALUE_4`: 현재 페이지 경로
- `CUSTOM_VALUE_5`: 타임스탬프

## 디버깅

### 콘솔 함수
브라우저 개발자 도구에서 다음 함수를 사용할 수 있습니다:

```javascript
debugUserInfo()  // 사용자 정보 및 ChannelIO 상태 확인
```

### 로그 확인
콘솔에서 다음 정보들을 확인할 수 있습니다:
- 사용자 정보 로드 과정
- ChannelIO 초기화 상태
- 에러 메시지

## 테스트

### 1. PHP 환경 테스트
각 PHP 페이지에 접속하여 ChannelIO가 정상적으로 초기화되는지 확인

### 2. HTML 테스트
`test_channelio.html` 파일을 브라우저에서 열어 테스트

### 3. 세션 테스트
로그인 후 세션 정보가 ChannelIO에 제대로 전달되는지 확인

## 주의사항

1. **세션 시작**: ChannelIO 스크립트를 사용하기 전에 `session_start()`가 호출되어야 합니다.

2. **경로 설정**: `require_once` 경로를 각 파일의 위치에 맞게 조정해야 합니다.

3. **보안**: 세션 정보가 클라이언트 사이드에 노출되므로 민감한 정보는 포함하지 마세요.

4. **중복 로드**: ChannelIO 스크립트가 중복으로 로드되지 않도록 주의하세요.

## 커스터마이징

### 플러그인 키 변경
`phpmodules/ChannelIO.php` 파일의 `$pluginKey` 변수를 수정하세요.

### 커스텀 값 추가
`generateChannelIOScript()` 함수에서 `CUSTOM_VALUE_` 항목을 추가하거나 수정하세요.

### 스타일링
ChannelIO 위젯의 스타일은 ChannelIO 대시보드에서 설정할 수 있습니다.

## 문제 해결

### ChannelIO가 초기화되지 않는 경우
1. 세션이 시작되었는지 확인
2. 브라우저 콘솔에서 에러 메시지 확인
3. `debugUserInfo()` 함수 실행하여 상태 확인

### 사용자 정보가 제대로 전달되지 않는 경우
1. 세션 변수들이 올바르게 설정되었는지 확인
2. PHP 오류 로그 확인
3. 세션 쿠키 설정 확인

## 지원

문제가 발생하거나 추가 기능이 필요한 경우, 다음을 확인해주세요:
1. 브라우저 콘솔의 에러 메시지
2. PHP 오류 로그
3. 세션 상태 및 변수 값
